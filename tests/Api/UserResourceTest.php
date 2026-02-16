<?php

namespace App\Tests\Api;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    public function testGetUserCollection(): void
    {
        UserFactory::createMany(5);

        $this->browser()->get('/api/users')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 5)
            ->assertJsonMatches('length("member")', 5)
            ->assertJsonMatches('keys("member"[0])', [
                '@id',
                '@type',
                'email',
                'nickName',
                'avatar',
                'verified',
                'notify',
                'categories',
                'tags',
                'country',
            ]);
    }

    public function testGetUserItem(): void
    {
        $user = UserFactory::createOne();
        $this->browser()->get('/api/users/'.$user->getId())
            ->assertJson()
            ->assertStatus(200);
    }

    public function testPutUserWithoutAuth(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->patch('/api/users/'.$user->getId(), [
                'json' => [
                    'nickName' => 'changed',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(401);
    }

}
