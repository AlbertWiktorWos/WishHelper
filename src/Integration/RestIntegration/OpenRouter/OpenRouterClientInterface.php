<?php

namespace App\Integration\RestIntegration\OpenRouter;

use App\Integration\RestIntegration\OpenRouter\DTO\ChatRequest;
use App\Integration\RestIntegration\OpenRouter\DTO\ChatResponse;

interface OpenRouterClientInterface
{
    public function createResponse(ChatRequest $request): ChatResponse;
}
