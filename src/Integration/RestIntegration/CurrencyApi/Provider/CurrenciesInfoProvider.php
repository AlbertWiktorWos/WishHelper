<?php

namespace App\Integration\RestIntegration\CurrencyApi\Provider;

use App\Integration\RestIntegration\CurrencyApi\CurrencyApiClientInterface;
use App\Integration\RestIntegration\CurrencyApi\DTO\CurrenciesRequest;
use App\Integration\RestIntegration\CurrencyApi\DTO\CurrenciesResponse;
use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use App\Integration\RestIntegration\CurrencyApi\ResourcesNames;
use Psr\Log\LoggerInterface;

class CurrenciesInfoProvider
{
    public function __construct(
        private CurrencyApiClientInterface $client,
        private LoggerInterface $logger,
    ) {
    }

    public function getCurrencies(array $codes = []): ?CurrenciesResponse
    {
        try {
            $request = new CurrenciesRequest($codes);
            $response = $this->client->getRequest(ResourcesNames::CURRENCIES, $request);

            return CurrenciesResponse::fromApi($response->toArray(false));
        } catch (CurrencyApiException $apiException) {
            // todo disable the worker
            $this->logger->error($apiException->getMessage());
            throw $apiException;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}
