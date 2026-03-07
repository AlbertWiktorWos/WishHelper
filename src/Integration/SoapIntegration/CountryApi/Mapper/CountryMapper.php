<?php

namespace App\Integration\SoapIntegration\CountryApi\Mapper;

use App\Integration\SoapIntegration\CountryApi\DTO\CountryInfo;

class CountryMapper
{
    /**
     * @return CountryInfo[]
     */
    public function mapAllCountries(array $countriesInfo): array
    {
        $countries = [];

        foreach ($countriesInfo as $data) {
            $country = $this->mapCountry($data);
            $countries[$country->code] = $country;
        }

        return $countries;
    }

    public function mapCountry(object $data): CountryInfo
    {
        $country = new CountryInfo();
        $country->code = $data->sISOCode ?? '';
        $country->name = $data->sName ?? '';
        $country->capital = $data->sCapitalCity ?? '';
        $country->phoneCode = $data->sPhoneCode ?? '';
        $country->continent = $data->sContinentCode ?? '';
        $country->flag = $data->sCountryFlag ?? '';
        $country->currency = $data->sCurrencyISOCode ?? null;

        if (!empty($data->Languages->tLanguage)) {
            $langs = $data->Languages->tLanguage;

            if (!is_array($langs)) {
                $langs = [$langs];
            }

            foreach ($langs as $lang) {
                $country->languages[] = $lang->sName ?? $lang->sISOCode;
            }
        }

        return $country;
    }
}
