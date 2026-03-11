<?php

namespace App\Integration\RestIntegration\OpenRouter\DTO;

class ChatAnswerDto
{
    private array|string $response;

    public function __construct(array|string $response)
    {
        $this->response = $response;
    }

    public function isArray(): bool
    {
        return is_array($this->response);
    }

    public function getArray(): array
    {
        if (!$this->isArray()) {
            throw new \LogicException('Response is not an array');
        }

        return $this->response;
    }

    public function getString(): string
    {
        if ($this->isArray()) {
            throw new \LogicException('Response is not a string');
        }

        return $this->response;
    }

    public function getContent(): string|array
    {
        return $this->response;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function fromString(string $data): self
    {
        return new self($data);
    }
}
