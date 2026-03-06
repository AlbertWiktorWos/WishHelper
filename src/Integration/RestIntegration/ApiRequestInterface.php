<?php

namespace App\Integration\RestIntegration;

interface ApiRequestInterface
{
    public function toQuery(): array;
}
