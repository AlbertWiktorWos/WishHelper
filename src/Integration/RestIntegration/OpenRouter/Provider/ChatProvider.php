<?php

namespace App\Integration\RestIntegration\OpenRouter\Provider;

use App\Integration\RestIntegration\OpenRouter\DTO\ChatAnswerDto;
use App\Integration\RestIntegration\OpenRouter\DTO\ChatRequest;
use App\Integration\RestIntegration\OpenRouter\OpenRouterClientInterface;
use App\Integration\RestIntegration\OpenRouter\ResponseFormatTypes;
use Psr\Log\LoggerInterface;

class ChatProvider
{
    public function __construct(
        private OpenRouterClientInterface $client,
        private string $model,
        private LoggerInterface $logger,
    ) {
    }

    public function ask(string $prompt, ?ResponseFormatTypes $responseFormatTypes = null): ChatAnswerDto
    {
        /**
         * Example prompt
         * User chosen country is Poland. User chosen categories are Books. User chosen tags are criminal. User request: something more romantic. Based on the information above prepare idea for the wish. The price should not be greater than 1000 PLN.
         * Return ONLY valid JSON in this format:
         *
         * {
         * "title": "title of the product",
         * "description": "description of the product",
         * "price": "approximate price of the product",
         * "currency": "currency iso code of the price of the product"
         * "category": "one of these categories: Books"
         * }
         * */
        $request = new ChatRequest(
            $this->model,
            [
                [
                    'type' => 'message',
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            $responseFormatTypes
        );

        $response = $this->client->createResponse($request);
        $content = $response->getContent();
        if ($responseFormatTypes && ResponseFormatTypes::JSON_RESPONSE == $responseFormatTypes) {
            $content = $this->extractJson($content);
        } elseif (is_string($content)) {
            return ChatAnswerDto::fromString($content);
        }

        return ChatAnswerDto::fromArray($content);
    }

    private function extractJson(string $text): ?array
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if (false === $start || false === $end) {
            return null;
        }

        $json = substr($text, $start, $end - $start + 1);

        $decoded = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->error(json_last_error());
            throw new \Exception(json_last_error());
        }

        return $decoded;
    }
}
