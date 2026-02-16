<?php

namespace App\Tests\Api;

use App\Factory\CategoryFactory;
use Zenstruck\Browser\Json;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CategoryResourceTest extends ApiTestCase
{
    use ResetDatabase;  // Trait to reset the database before each test

    use Factories; // Trait to use Zenstruck Foundry factories

    public function testGetCategoryCollection(): void
    {
        CategoryFactory::createMany(5);

        $this->browser()->get('/api/categories')
            ->assertJson()
            ->assertJsonMatches('"totalItems"', 5)
            ->assertJsonMatches('length("member")', 5)
            ->use(function (Json $json) {
                $json->assertMatches('keys("member"[0])', [
                    '@id',
                    '@type',
                    'name',
                    'icon',
                ]); // assert that all expected fields are present
            }) // for learning purposes we use ->use() to show alternative way of using the json assertions
        ;
    }

    public function testGetCategoryItem(): void
    {
        $category = CategoryFactory::createOne();
        $this->browser()->get('/api/categories/'.$category->getId())
            ->assertJson()
            ->assertStatus(200);
    }
}
