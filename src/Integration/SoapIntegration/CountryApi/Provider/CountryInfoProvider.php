<?php

namespace App\Integration\SoapIntegration\CountryApi\Provider;

use App\Integration\SoapIntegration\CountryApi\CountryCommInterface;
use App\Integration\SoapIntegration\CountryApi\DTO\CountryInfo;
use App\Integration\SoapIntegration\CountryApi\Mapper\CountryMapper;

class CountryInfoProvider
{
    public function __construct(
        private CountryCommInterface $comm,
        private CountryMapper $mapper,
    ) {
    }

    /**
     * @param string[] $codes
     *
     * @return CountryInfo[]
     */
    public function getCountries(array $codes = []): ?array
    {
        $rawData = $this->comm->getAllCountries();
        $countries = $this->mapper->mapAllCountries($rawData);

        if (empty($codes)) {
            return $countries;
        }

        $codes = array_map('strtoupper', $codes);

        return array_filter(
            $countries,
            fn (CountryInfo $c) => in_array($c->code, $codes, true)
        );
    }

    public function getCountryInfo(string $code): ?CountryInfo
    {
        $rawData = $this->comm->getCountryInfo($code);

        return $this->mapper->mapCountry($rawData);
    }
}
