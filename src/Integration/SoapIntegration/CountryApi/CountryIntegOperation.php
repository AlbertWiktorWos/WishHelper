<?php

namespace App\Integration\SoapIntegration\CountryApi;

enum CountryIntegOperation: string
{
    case FULL_COUNTRY_INFO_ALL = 'FullCountryInfoAllCountries';
    case FULL_COUNTRY_INFO = 'FullCountryInfo';
}
