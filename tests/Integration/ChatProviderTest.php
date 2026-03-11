<?php

namespace App\Tests\Integration;

use App\Integration\RestIntegration\OpenRouter\DTO\ChatAnswerDto;
use App\Integration\RestIntegration\OpenRouter\Exception\OpenRouterException;
use App\Integration\RestIntegration\OpenRouter\MockOpenRouterClient;
use App\Integration\RestIntegration\OpenRouter\Provider\ChatProvider;
use App\Integration\RestIntegration\OpenRouter\ResponseFormatTypes;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ChatProviderTest extends TestCase
{
    private ChatProvider $provider;

    protected function setUp(): void
    {
        $mockClient = new MockOpenRouterClient('http://fake');
        $this->provider = new ChatProvider($mockClient, 'gpt-4', new NullLogger());
    }

    public function testAskReturnsChatAnswerDto(): void
    {
        $prompt = 'Generate a mock wish item';

        $response = $this->provider->ask($prompt, ResponseFormatTypes::JSON_RESPONSE);

        $this->assertInstanceOf(ChatAnswerDto::class, $response);

        $content = $response->getContent();
        $this->assertIsArray($content);

        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('price', $content);
        $this->assertArrayHasKey('currency', $content);
        $this->assertArrayHasKey('category', $content);

        $this->assertEquals('Mock Wish Item', $content['title']);
        $this->assertEquals('Mock description generated in tests', $content['description']);
        $this->assertEquals('150', $content['price']);
        $this->assertEquals('PLN', $content['currency']);
        $this->assertEquals('Books', $content['category']);
    }

    public function testAskThrowsExceptionOnErrorResponse(): void
    {
        $this->expectException(OpenRouterException::class);

        // We call the prompt containing "error", which causes error 400 in MockOpenRouterClient
        $this->provider->ask('This will trigger an error');
    }

    public function testAskReturnsStringWhenNoJsonResponse(): void
    {
        $prompt = 'Just a normal prompt';

        // We use null for ResponseFormatTypes to get ChatAnswerDto with string
        $response = $this->provider->ask($prompt, null);

        $this->assertInstanceOf(ChatAnswerDto::class, $response);
        $this->assertIsString($response->getContent());
    }
}
