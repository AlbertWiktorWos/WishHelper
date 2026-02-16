<?php

namespace App\Tests\Api;

use App\Factory\CountryFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CountryResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    public function testGetCountryCollection(): void
    {
        CountryFactory::createMany(4);

        $this->browser()->get('/api/countries')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 4)
            ->assertJsonMatches('length("member")', 4)
            ->assertJsonMatches('keys("member"[0])', [
                '@id',
                '@type',
                'code',
                'name',
                'flag',
                'currency',
            ]);
    }

    public function testGetCountryItem(): void
    {
        $country = CountryFactory::createOne();
        $this->browser()->get('/api/countries/'.$country->getId())
            ->assertJson()
            ->assertStatus(200);
    }
}
