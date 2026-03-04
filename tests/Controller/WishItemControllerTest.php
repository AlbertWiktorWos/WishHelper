<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class WishItemControllerTest extends WebTestCase
{
    use ResetDatabase;  // Trait to reset the database before each test
    use Factories;

    public function testIndex(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne()->_real();

        $client->loginUser($user);

        $client->request('GET', '/wishitem/mine');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'wishItemMine',
            $client->getResponse()->getContent()
        );
    }
}
