<?php

namespace App\Integration\RestIntegration\CurrencyApi;

use App\Integration\RestIntegration\ApiGetRequestInterface;
use App\Integration\RestIntegration\CurrencyApi\Exception\CurrencyApiException;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface CurrencyApiClientInterface
{
    /**
     * @throws CurrencyApiException
     */
    public function getRequest(ResourcesNames $resourceName, ApiGetRequestInterface $request): ResponseInterface;
}
