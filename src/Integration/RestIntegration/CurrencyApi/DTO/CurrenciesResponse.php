<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiResponseInterface;

class CurrenciesResponse implements ApiResponseInterface
{
    /** @var CurrencyDto[] */
    public array $currencies = [];

    public function __construct(array $currencies)
    {
        $this->currencies = $currencies;
    }

    public static function fromApi(array $data): ?self
    {
        $currencies = [];

        foreach ($data['data'] as $code => $currency) {
            $currencies[$code] = CurrencyDto::fromApi($code, $currency);
        }

        return new self($currencies);
    }
}
