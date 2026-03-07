<?php

namespace App\Integration\SoapIntegration\CountryApi;

use App\Integration\SoapIntegration\SoapClientAdapter;
use Psr\Log\LoggerInterface;

class CountryComm implements CountryCommInterface
{
    private SoapClientAdapter $client;

    public function __construct(
        LoggerInterface $logger,
        string $wsdl,
        int $retries = 3,
    ) {
        $this->client = new SoapClientAdapter($logger, $wsdl, $retries);
    }

    public function getAllCountries(): array
    {
        $response = $this->client->call(
            CountryIntegOperation::FULL_COUNTRY_INFO_ALL->value
        );

        return $response->FullCountryInfoAllCountriesResult->tCountryInfo ?? [];
    }

    public function getCountryInfo(string $code): ?object
    {
        $response = $this->client->call(
            CountryIntegOperation::FULL_COUNTRY_INFO->value,
            ['sCountryISOCode' => $code]
        );

        return $response->FullCountryInfoResult ?? null;
    }
}
