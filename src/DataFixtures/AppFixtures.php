<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Country;
use App\Entity\Currency;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use App\Factory\WishItemFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    // Categories
    public const CATEGORY_HOBBY = ['name' => 'Hobby', 'icon' => 'bi-brush'];
    public const CATEGORY_ELECTRONICS = ['name' => 'Electronics', 'icon' => 'bi-phone'];
    public const CATEGORY_BOOKS = ['name' => 'Books', 'icon' => 'bi-book'];
    public const CATEGORY_GAMES = ['name' => 'Games', 'icon' => 'bi-controller'];

    public const CATEGORIES = [
        self::CATEGORY_HOBBY,
        self::CATEGORY_ELECTRONICS,
        self::CATEGORY_BOOKS,
        self::CATEGORY_GAMES,
    ];

    // Currencies
    public const CURRENCY_USD = ['code' => 'USD', 'name' => 'Dollar', 'symbol' => '$', 'rate' => '1'];
    public const CURRENCY_EUR = ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate' => '0.93'];
    public const CURRENCY_GBP = ['code' => 'GBP', 'name' => 'Pound', 'symbol' => '£', 'rate' => '0.73'];
    public const CURRENCY_PLN = ['code' => 'PLN', 'name' => 'Złoty', 'symbol' => 'zł', 'rate' => '3.55'];

    public const CURRENCIES = [
        'USD' => self::CURRENCY_USD,
        'EUR' => self::CURRENCY_EUR,
        'GBP' => self::CURRENCY_GBP,
        'PLN' => self::CURRENCY_PLN,
    ];

    // Countries
    public const COUNTRY_US = ['code' => 'US', 'name' => 'United States', 'flag' => 'https://flagicons.lipis.dev/flags/4x3/us.svg', 'continent' => 'North America', 'currency' => 'USD'];
    public const COUNTRY_GERMANY = ['code' => 'DE', 'name' => 'Germany', 'flag' => 'https://flagicons.lipis.dev/flags/4x3/de.svg', 'continent' => 'Europe', 'currency' => 'EUR'];
    public const COUNTRY_GB = ['code' => 'GB', 'name' => 'United Kingdom', 'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb.svg', 'continent' => 'Europe', 'currency' => 'GBP'];
    public const COUNTRY_PL = ['code' => 'PL', 'name' => 'Poland', 'flag' => 'https://flagicons.lipis.dev/flags/4x3/pl.svg', 'continent' => 'Europe', 'currency' => 'PLN'];

    public const COUNTRIES = [
        'US' => self::COUNTRY_US,
        'DE' => self::COUNTRY_GERMANY,
        'GB' => self::COUNTRY_GB,
        'PL' => self::COUNTRY_PL,
    ];

    public function load(ObjectManager $manager): void
    {
        $currencies = [];
        foreach (self::CURRENCIES as $data) {
            $currency = new Currency();
            $currency->setCode($data['code'])
                ->setName($data['name'])
                ->setSymbol($data['symbol'])
                ->setExchangeRate($data['rate'])
                ->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($currency);
            $currencies[$data['code']] = $currency;
        }

        $countries = [];
        foreach (self::COUNTRIES as $data) {
            $country = new Country();
            $country->setCode($data['code'])
                ->setName($data['name'])
                ->setFlag($data['flag'])
                ->setContinent($data['continent'])
                ->setCurrency($currencies[$data['currency']])
                ->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($country);
            $countries[] = $country;
        }

        $categories = [];
        foreach (self::CATEGORIES as $data) {
            $category = new Category();
            $category->setName($data['name'])
                ->setIcon($data['icon']);
            $manager->persist($category);
            $categories[] = $category;
        }

        TagFactory::new()->createMany(40); // 20 random tags

        // we add admin user
        UserFactory::createOne([
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'categories' => [$categories[array_rand($categories)]],
            'country' => $countries[array_rand($countries)],
        ]);

        // --- UŻYTKOWNICY ---
        UserFactory::new()->createMany(20, function () use ($countries, $categories) {
            $randomCountry = $countries[array_rand($countries)];
            $randomCategory = $categories[array_rand($categories)];

            return [
                'country' => $randomCountry,
                'categories' => [$randomCategory],
                'tags' => TagFactory::randomRange(0, 5),
            ];
        });

        WishItemFactory::new()->createMany(100, function () use ($categories, $currencies) {
            $randomCategory = $categories[array_rand($categories)];
            $randomCurrency = $currencies[array_rand($currencies)];

            return [
                'owner' => UserFactory::random(),
                'category' => $randomCategory,
                'tags' => TagFactory::randomRange(0, 3),
                'currency' => $randomCurrency,
            ];
        });

        $manager->flush();
    }
}
