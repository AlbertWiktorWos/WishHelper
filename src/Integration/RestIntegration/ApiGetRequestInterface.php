<?php

namespace App\Integration\RestIntegration;

interface ApiGetRequestInterface
{
    public function toQuery(): array;
}
