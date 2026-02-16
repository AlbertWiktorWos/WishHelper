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

    /** --- USER --- */
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

    /** --- WISH ITEM --- */
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
}
