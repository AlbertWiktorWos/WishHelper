<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiGetRequestInterface;

class CurrenciesRequest implements ApiGetRequestInterface
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
