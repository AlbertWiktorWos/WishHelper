<?php

namespace App\Tests\Api;

use App\Factory\CurrencyFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CurrencyResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    /** --- CURRENCY --- */
    public function testGetCurrencyCollection(): void
    {
        CurrencyFactory::createMany(4);
        $user = UserFactory::createOne();

        $this->browser()->actingAs($user)->get('/api/currencies')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 5) // 4 + 1 from user
            ->assertJsonMatches('length("member")', 5)
            ->assertJsonMatches('keys("member"[0])', [
                '@id',
                '@type',
                'id',
                'code',
                'name',
                'symbol',
                'exchangeRate',
                'updatedAt',
            ]);
    }

    public function testGetCurrencyItem(): void
    {
        $user = UserFactory::createOne();
        $currency = CurrencyFactory::createOne();
        $this->browser()->actingAs($user)->get('/api/currencies/'.$currency->getId())
            ->assertJson()
            ->assertStatus(200);
    }
}
