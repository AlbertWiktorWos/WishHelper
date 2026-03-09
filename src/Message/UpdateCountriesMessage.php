<?php

namespace App\Message;

final class UpdateCountriesMessage
{
    public function __construct(
        public readonly array $codes = []
    ) {}
}