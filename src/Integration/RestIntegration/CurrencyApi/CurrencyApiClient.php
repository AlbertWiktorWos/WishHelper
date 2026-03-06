<?php

namespace App\Integration\RestIntegration\CurrencyApi;

use App\Integration\RestIntegration\ApiRequestInterface;
use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CurrencyApiClient implements CurrencyApiClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey,
        private string $baseUrl,
    ) {
    }

    public function getRequest(ResourcesNames $resourceName, ApiRequestInterface $request): ResponseInterface
    {
        $query = $request->toQuery();
        $query['apikey'] = $this->apiKey;

        $response = $this->client->request(
            'GET',
            sprintf('%s/%s', $this->baseUrl, $resourceName->value),
            [
                'query' => $query,
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new CurrencyApiException(sprintf('Currency API request failed for resource "%s"', $resourceName->value));
        }

        return $response;
    }
}
