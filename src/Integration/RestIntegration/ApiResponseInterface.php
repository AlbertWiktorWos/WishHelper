<?php

namespace App\Integration\RestIntegration;

interface ApiResponseInterface
{
    public static function fromApi(array $data): ?self;
}
