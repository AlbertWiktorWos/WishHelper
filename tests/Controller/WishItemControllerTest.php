<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WishItemControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/wishitem/mine');
        $response = $client->getResponse();
        self::assertStringContainsString('wishItemMine', $response->getContent());
        self::assertResponseIsSuccessful();
    }
}
