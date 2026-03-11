<?php

namespace App\Integration\RestIntegration\OpenRouter;

use App\Integration\RestIntegration\OpenRouter\DTO\ChatRequest;
use App\Integration\RestIntegration\OpenRouter\DTO\ChatResponse;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenRouterClient implements OpenRouterClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private string $apiKey,
        private string $baseUrl,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function createResponse(ChatRequest $request): ChatResponse
    {
        try {
            /** json_encode($request->toArray())
             * {"model":"nvidia\/nemotron-3-nano-30b-a3b:free","input":[{"type":"message","role":"user","content":"User chosen country is Poland. User chosen categories are Books. User chosen tags are criminal. User request: something more romantic. Based on the information above prepare idea for the wish. The price should not be greater than 1000 PLN. \nReturn ONLY valid JSON in this format:\n\n{\n\"title\": \"title of the product\",\n\"description\": \"description of the product\",\n\"price\": \"approximate price of the product\",\n\"currency\": \"currency iso code of the price of the product\"\n\"category\": \"one of these categories: Books\"\n}\n"}],"response_format":{"type":"json_object"}}.
             */
            $response = $this->client->request('POST', $this->baseUrl.'/responses', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $request->toArray(),
            ]);
            $data = $response->toArray();
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), [
                'code' => $exception->getResponse()->getStatusCode(),
                'content' => $exception->getResponse()->getContent(false),
            ]);
            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $exception;
        }

        return ChatResponse::fromApi($data);
    }
}
