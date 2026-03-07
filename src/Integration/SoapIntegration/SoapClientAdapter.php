<?php

namespace App\Integration\SoapIntegration;

use App\Integration\SoapIntegration\CountryApi\Exception\CountryApiException;
use Psr\Log\LoggerInterface;

class SoapClientAdapter
{
    private ?\SoapClient $client = null;

    private string $wsdl;
    private int $retries;
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        string $wsdl,
        int $retries = 3,
    ) {
        $this->wsdl = $wsdl;
        $this->retries = $retries;
        $this->logger = $logger;
    }

    private function getClient(): \SoapClient
    {
        if (!$this->client) {
            $this->client = new \SoapClient($this->wsdl, [
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
            ]);
        }

        return $this->client;
    }

    public function call(string $operation, array $params = []): mixed
    {
        if (!$this->client) {
            $this->client = $this->getClient();
        }

        for ($attempt = 1; $attempt <= $this->retries; ++$attempt) {
            try {
                $this->logger->info('SOAP request', [
                    'operation' => $operation,
                    'params' => $params,
                    'attempt' => $attempt,
                ]);

                $response = $this->getClient()->__soapCall($operation, []);

                $this->logger->debug('SOAP response', [
                    'operation' => $operation,
                    'response' => $response,
                ]);

                return $response;
            } catch (\SoapFault $e) {
                $this->logger->warning('SOAP request failed', [
                    'operation' => $operation,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                $this->logger->debug('SOAP raw request', [
                    'xml' => $this->client->__getLastRequest() ?: null,
                ]);

                $this->logger->debug('SOAP raw response', [
                    'xml' => $this->client->__getLastResponse() ?: null,
                ]);

                if ($attempt === $this->retries) {
                    $this->logger->error(sprintf('SOAP request failed after %d attempt%s', $attempt, $attempt > 1 ? 's' : ''), [
                        'operation' => $operation,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    throw new CountryApiException('Country Integration Error', 0, $e);
                }

                usleep(200000);
            }
        }

        return false;
    }
}
