<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiRequestInterface;

class CurrenciesRequest implements ApiRequestInterface
{
    public function __construct(
        public readonly array $currencies = [],
    ) {
    }

    public function toQuery(): array
    {
        if (empty($this->currencies)) {
            return [];
        }

        return [
            'currencies' => implode(',', $this->currencies),
        ];
    }
}
