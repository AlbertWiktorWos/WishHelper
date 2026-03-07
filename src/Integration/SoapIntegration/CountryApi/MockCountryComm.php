<?php

namespace App\Integration\SoapIntegration\CountryApi;

use Psr\Log\LoggerInterface;

class MockCountryComm implements CountryCommInterface
{
    public function __construct(
        LoggerInterface $logger,
        string $wsdl = 'test',
        int $retries = 0,
    ) {
    }

    public function getAllCountries(): array
    {
        $response = $this->buildAllCountriesResponse();

        return $response->FullCountryInfoAllCountriesResult->tCountryInfo ?? [];
    }

    public function getCountryInfo(string $code): ?object
    {
        $response = $this->buildCountryResponse($code);

        return $response->FullCountryInfoResult ?? null;
    }

    private function buildAllCountriesResponse(): object
    {
        $response = new \stdClass();
        $result = new \stdClass();

        $result->tCountryInfo = [
            $this->prepareStdClassOfCountry('PL', 'Poland', 'Warsaw', '48', 'EU', 'PLN'),
            $this->prepareStdClassOfCountry('DE', 'Germany', 'Berlin', '49', 'EU', 'EUR'),
            $this->prepareStdClassOfCountry('US', 'United States', 'Washington', '1', 'NA', 'USD'),
        ];

        $response->FullCountryInfoAllCountriesResult = $result;

        return $response;
    }

    private function buildCountryResponse(?string $code): object
    {
        $countries = [
            'PL' => $this->prepareStdClassOfCountry('PL', 'Poland', 'Warsaw', '48', 'EU', 'PLN'),
            'DE' => $this->prepareStdClassOfCountry('DE', 'Germany', 'Berlin', '49', 'EU', 'EUR'),
            'US' => $this->prepareStdClassOfCountry('US', 'United States', 'Washington', '1', 'NA', 'USD'),
        ];

        if (!isset($countries[$code])) {
            $response = new \stdClass();
            $response->FullCountryInfoResult = $this->prepareStdClassOfCountry(
                '', 'Country not found in the database', '', '', '', ''
            );

            return $response;
        }

        $response = new \stdClass();
        $response->FullCountryInfoResult = $countries[$code];

        return $response;
    }

    private function prepareStdClassOfCountry(
        string $code,
        string $name,
        string $capital,
        string $phone,
        string $continent,
        string $currency,
    ): object {
        $c = new \stdClass();

        $c->sISOCode = $code;
        $c->sName = $name;
        $c->sCapitalCity = $capital;
        $c->sPhoneCode = $phone;
        $c->sContinentCode = $continent;
        $c->sCountryFlag = '';
        $c->sCurrencyISOCode = $currency;

        $lang = new \stdClass();
        $lang->sISOCode = strtolower($c->sName);
        $lang->sName = $c->sName.'ish';

        $langs = new \stdClass();
        $langs->tLanguage = [$lang];

        $c->Languages = $langs;

        return $c;
    }
}
