<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiGetRequestInterface;

class LatestRatesRequest implements ApiGetRequestInterface
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
