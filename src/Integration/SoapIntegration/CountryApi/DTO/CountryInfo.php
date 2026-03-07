<?php

namespace App\Integration\SoapIntegration\CountryApi\DTO;

class CountryInfo
{
    public string $code;
    public string $name;
    public string $capital;
    public string $phoneCode;
    public string $continent;
    public string $flag;
    public ?string $currency = null;
    public array $languages = []; // optional, array of strings
}
