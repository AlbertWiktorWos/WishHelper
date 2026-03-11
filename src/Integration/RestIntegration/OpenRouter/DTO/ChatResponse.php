<?php

namespace App\Integration\RestIntegration\OpenRouter\DTO;

use App\Integration\RestIntegration\ApiResponseInterface;

class ChatResponse implements ApiResponseInterface
{
    public function __construct(
        private array $responseArray,
        private ?string $content,
    ) {
    }

    public function getResponseData(): array
    {
        return $this->responseArray;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public static function fromApi(array $data): self
    {
        $output = $data['output'] ?? [];
        foreach ($output as $item) {
            if ('message' === $item['type']) {
                return new self(
                    $data,
                    $item['content'][0]['text'] ?? null
                );
            }
        }

        return new self(
            $data, null
        );
    }
}
