<?php

namespace App\Integration\SoapIntegration\CountryApi;

use Psr\Log\LoggerInterface;

interface CountryCommInterface
{
    public function __construct(
        LoggerInterface $logger,
        string $wsdl,
        int $retries,
    );

    public function getAllCountries(): array;

    public function getCountryInfo(string $code): ?object;
}
