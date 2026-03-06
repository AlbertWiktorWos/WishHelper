<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

use App\Integration\RestIntegration\ApiResponseInterface;

class LatestRatesResponse implements ApiResponseInterface
{
    public function __construct(
        public readonly string $base = 'USD',
        public readonly array $rates = [],
    ) {
    }

    public static function fromApi(array $data): ?self
    {
        if (empty($data['data'])) {
            return null;
        }
        $base = 'USD';
        foreach ($data['data'] as $code => $currencyRate) {
            if (1 === $currencyRate) {
                $base = $code;
            }
        }

        return new self(
            $base,
            $data['data']
        );
    }
}
