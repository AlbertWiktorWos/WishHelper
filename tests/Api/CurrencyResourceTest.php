<?php

namespace App\Tests\Api;

use App\Factory\CurrencyFactory;
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

        $this->browser()->get('/api/currencies')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 4)
            ->assertJsonMatches('length("member")', 4)
            ->assertJsonMatches('keys("member"[0])', [
                '@id',
                '@type',
                'code',
                'name',
                'symbol',
                'exchangeRate',
                'updatedAt',
            ]);
    }

    public function testGetCurrencyItem(): void
    {
        $currency = CurrencyFactory::createOne();
        $this->browser()->get('/api/currencies/'.$currency->getId())
            ->assertJson()
            ->assertStatus(200);
    }
}
