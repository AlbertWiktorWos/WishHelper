<?php

namespace App\Tests\Api;

use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TagResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    /** --- TAG --- */
    public function testGetTagCollection(): void
    {
        TagFactory::createMany(10);
        $user = UserFactory::createOne();

        $this->browser()->actingAs($user)->get('/api/tags')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 11) // 10 + 1 from user
            ->assertJsonMatches('length("member")', 11)
            ->assertJsonMatches('keys("member"[0])', [
                '@id',
                '@type',
                'name',
            ]);
    }

    public function testGetTagItem(): void
    {
        $tag = TagFactory::createOne();
        $user = UserFactory::createOne();
        $this->browser()->actingAs($user)->get('/api/tags/'.$tag->getId())
            ->assertJson()
            ->assertStatus(200);
    }

    public function testCreateTag(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->post('/api/tags', [
                'json' => [
                    'name' => 'electronics',
                ],
            ])
            ->assertStatus(201)
            ->assertJsonMatches('name', 'electronics');
    }

    public function testCreateTagFailsWhenNameBlank(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->post('/api/tags', [
                'json' => [
                    'name' => '',
                ],
            ])
            ->assertStatus(422) // ApiPlatform validation error
            ->assertJsonMatches('violations[0].propertyPath', 'name');
    }

    public function testCreateTagFailsWhenNameTooLong(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->post('/api/tags', [
                'json' => [
                    'name' => str_repeat('a', 101),
                ],
            ])
            ->assertStatus(422)
            ->assertJsonMatches('violations[0].propertyPath', 'name');
    }

    public function testPatchTag(): void
    {
        $tag = TagFactory::createOne(['name' => 'old']);

        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch('/api/tags/'.$tag->getId(), [
                'json' => [
                    'name' => 'new',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('name', 'new');
    }

    public function testPatchTagSameName(): void
    {
        $tag1 = TagFactory::createOne(['name' => 'test1']);
        $tag2 = TagFactory::createOne(['name' => 'test2']);
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch('/api/tags/'.$tag2->getId(), [
                'json' => [
                    'name' => 'test1',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(422);
    }

    public function testGetNonExistingTag(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->get('/api/tags/999999')
            ->assertStatus(404);
    }
}
