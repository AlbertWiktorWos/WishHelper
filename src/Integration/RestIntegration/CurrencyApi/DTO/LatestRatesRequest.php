<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiRequestInterface;

class LatestRatesRequest implements ApiRequestInterface
{
    public function __construct(
        public readonly string $baseCurrency,
        public readonly array $currencies = [],
    ) {
    }

    public function toQuery(): array
    {
        $query = [
            'base_currency' => $this->baseCurrency,
        ];

        if ($this->currencies) {
            $query['currencies'] = implode(',', $this->currencies);
        }

        return $query;
    }
}
