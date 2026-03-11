<?php

namespace App\Message;

final class UpdateCurrenciesMessage
{
    public function __construct(
        public readonly array $codes = [],
    ) {
    }
}
