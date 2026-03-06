<?php

namespace App\Integration\RestIntegration\CurrencyApi\DTO;

class CurrencyDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $symbol,
        public readonly ?int $decimalDigits = null,
        public readonly ?string $type = null,
    ) {
    }

    public static function fromApi(string $code, array $data): self
    {
        return new self(
            $code,
            $data['name'],
            $data['symbol'],
            $data['decimal_digits'],
            $data['type']
        );
    }
}
