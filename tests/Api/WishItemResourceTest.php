<?php

namespace App\Tests\Api;

use App\Factory\UserFactory;
use App\Factory\WishItemFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishItemResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    public function testGetWishItemCollection(): void
    {
        WishItemFactory::createMany(15);

        $this->browser()->get('/api/wish_items')
            ->assertJson()
            ->assertStatus(401); // now we have security on this endpoint so we need to be authenticated to access it, so we expect 401 Unauthorized status code
        // ->assertJsonMatches('"totalItems"', 15)
        // ->assertJsonMatches('length("member")', 15)
        // ->assertJsonMatches('keys("member"[0])', [
        //     '@id',
        //     '@type',
        //     'title',
        //     'description',
        //     'price',
        //     'link',
        //     'shared',
        //     'createdAt',
        //     'updatedAt',
        //     'category',
        //     'tags',
        //     'owner',
        //     'currency',
        // ]);
    }

    public function testGetWishItem(): void
    {
        $wish = WishItemFactory::createOne();
        $this->browser()->get('/api/wish_items/'.$wish->getId())
            ->assertJson()
            ->assertStatus(401);
    }

    public function testCreateWishItemWithoutAuth(): void
    {
        $this->browser()
            ->post('/api/wish_items', [
                'json' => [
                    'title' => 'Test',
                    'price' => '10.00',
                ],
            ])
            ->assertStatus(401);
    }

    public function testAuthenticatedUserCanGetWishCollection(): void
    {
        $user = UserFactory::createOne();
        WishItemFactory::createMany(5);

        $this->browser()
            ->actingAs($user)
            ->get('/api/wish_items')
            ->assertStatus(200)
            ->assertJsonMatches('"totalItems"', 5);
    }

    public function testUserCanUpdateOwnWish(): void
    {
        $user = UserFactory::createOne();
        $wish = WishItemFactory::createOne([
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/wish_items/'.$wish->getId(), [
                'json' => [
                    'title' => 'Updated title',
                    'price' => '20.00',
                    'shared' => true,
                    'category' => '/api/categories/1',
                    'currency' => '/api/currencies/1',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(200)
            ->assertJsonMatches('title', 'Updated title');
    }

    public function testUserCannotUpdateOtherUserWish(): void
    {
        $owner = UserFactory::createOne();
        $other = UserFactory::createOne();

        $wish = WishItemFactory::createOne([
            'owner' => $owner,
        ]);

        $this->browser()
            ->actingAs($other)
            ->patch('/api/wish_items/'.$wish->getId(), [
                'json' => [
                    'title' => 'Hacked',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->dump()
            ->assertStatus(403);
    }

    public function testUserCanDeleteOwnWish(): void
    {
        $user = UserFactory::createOne();
        $wish = WishItemFactory::createOne([
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs($user)
            ->delete('/api/wish_items/'.$wish->getId())
            ->assertStatus(204);
    }

    public function testUserCannotDeleteOtherWish(): void
    {
        $owner = UserFactory::createOne();
        $other = UserFactory::createOne();

        $wish = WishItemFactory::createOne([
            'owner' => $owner,
        ]);

        $this->browser()
            ->actingAs($other)
            ->delete('/api/wish_items/'.$wish->getId())
            ->assertStatus(403);
    }

}
