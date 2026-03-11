<?php

namespace App\Integration\RestIntegration\OpenRouter;

use App\Integration\RestIntegration\OpenRouter\DTO\ChatRequest;
use App\Integration\RestIntegration\OpenRouter\DTO\ChatResponse;
use App\Integration\RestIntegration\OpenRouter\Exception\OpenRouterException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MockOpenRouterClient implements OpenRouterClientInterface
{
    public function __construct(
        private string $baseUrl,
    ) {
    }

    public function createResponse(ChatRequest $request): ChatResponse
    {
        $mockHttpClient = new MockHttpClient();

        $response = $this->buildResponse($request);

        $mockHttpClient->setResponseFactory([$response]);

        $response = $mockHttpClient->request(
            'POST',
            $this->baseUrl.'/responses',
            [
                'json' => $request->toArray(),
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new OpenRouterException(sprintf('OpenRouter request failed with status %s', $response->getStatusCode()));
        }

        return ChatResponse::fromApi($response->toArray());
    }

    private function buildResponse(ChatRequest $request): MockResponse
    {
        $prompt = $request->getMessages()[0]['content'] ?? '';

        if ($this->isErrorScenario($prompt)) {
            return $this->getErrorResponse();
        }

        return new MockResponse(
            json_encode($this->getSuccessData()),
            ['http_code' => 200]
        );
    }

    private function isErrorScenario(string $prompt): bool
    {
        return str_contains($prompt, 'error');
    }

    private function getErrorResponse(): MockResponse
    {
        return new MockResponse(
            json_encode([
                'error' => [
                    'code' => 0,
                    'message' => 'Mock API error',
                    'metadata' => [
                        'mock' => true,
                    ],
                ],
                'user_id' => 'mock-user',
            ]),
            ['http_code' => 400]
        );
    }

    private function getSuccessData(): array
    {
        return [
            'completed_at' => 1.1,
            'created_at' => 1704067200,
            'frequency_penalty' => 1.1,
            'id' => 'resp-mock123',
            'instructions' => 'string',
            'model' => 'gpt-4',
            'object' => 'response',
            'parallel_tool_calls' => true,
            'presence_penalty' => 1.1,
            'status' => 'completed',
            'tool_choice' => 'auto',
            'tools' => [],
            'output' => [
                [
                    'id' => 'msg-mock123',
                    'role' => 'assistant',
                    'type' => 'message',
                    'status' => 'completed',
                    'content' => [
                        [
                            'type' => 'output_text',
                            'text' => json_encode([
                                'title' => 'Mock Wish Item',
                                'description' => 'Mock description generated in tests',
                                'price' => '150',
                                'currency' => 'PLN',
                                'category' => 'Books',
                            ]),
                            'annotations' => [],
                        ],
                    ],
                ],
            ],
            'usage' => [
                'input_tokens' => 10,
                'input_tokens_details' => [
                    'cached_tokens' => 0,
                ],
                'output_tokens' => 25,
                'output_tokens_details' => [
                    'reasoning_tokens' => 0,
                ],
                'total_tokens' => 35,
            ],
        ];
    }
}
