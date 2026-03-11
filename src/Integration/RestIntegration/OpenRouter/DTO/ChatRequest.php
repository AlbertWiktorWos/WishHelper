<?php

namespace App\Integration\RestIntegration\OpenRouter\DTO;

use App\Integration\RestIntegration\ApiPostRequestInterface;
use App\Integration\RestIntegration\OpenRouter\ResponseFormatTypes;

class ChatRequest implements ApiPostRequestInterface
{
    public function __construct(
        private string $model,
        private array $messages,
        private ?ResponseFormatTypes $responseFormat = null,
    ) {
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getResponseFormat(): ?ResponseFormatTypes
    {
        return $this->responseFormat;
    }

    public function toArray(): array
    {
        $array = [
            'model' => $this->model,
            'input' => $this->messages,
        ];

        if ($this->responseFormat) {
            $array['response_format'] = [
                'type' => $this->responseFormat->value,
            ];
        }

        return $array;
    }
}
