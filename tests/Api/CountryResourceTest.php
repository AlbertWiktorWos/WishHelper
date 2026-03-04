<?php

namespace App\Tests\Api;

use App\Factory\CountryFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CountryResourceTest extends ApiTestCase
{
    use ResetDatabase; // Trait to reset the database before each test
    use Factories; // Trait to use Zenstruck Foundry factories

    public function testGetCountryCollection(): void
    {
        CountryFactory::createMany(4);
        $user = UserFactory::createOne();

        $this->browser()->actingAs($user)->get('/api/countries')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 5) // 4+1 from user
            ->assertJsonMatches('length("member")', 5)
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
        $user = UserFactory::createOne();
        $this->browser()->actingAs($user)->get('/api/countries/'.$country->getId())
            ->assertJson()
            ->assertStatus(200);
    }
}
