<?php

namespace App\Integration\RestIntegration\CurrencyApi\Provider;

use App\Integration\RestIntegration\CurrencyApi\CurrencyApiClientInterface;
use App\Integration\RestIntegration\CurrencyApi\DTO\LatestRatesRequest;
use App\Integration\RestIntegration\CurrencyApi\DTO\LatestRatesResponse;
use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use App\Integration\RestIntegration\CurrencyApi\ResourcesNames;
use Psr\Log\LoggerInterface;

class CurrencyRatesProvider
{
    public function __construct(
        private CurrencyApiClientInterface $client,
        private string $defaultBaseCurrency,
        private LoggerInterface $logger,
    ) {
    }

    public function getRates(?string $base = null, $codes = []): ?LatestRatesResponse
    {
        try {
            $request = new LatestRatesRequest($base ?? $this->defaultBaseCurrency, $codes);
            $data = $this->client->getRequest(ResourcesNames::LATEST, $request);

            return LatestRatesResponse::fromApi($data->toArray(false));
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
