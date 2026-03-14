<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Country;
use App\Entity\Currency;
use App\EventSubscriber\TagCleanupSubscriber;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use App\Factory\WishItemFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture
{
    // Categories
    public const CATEGORY_HOBBY = ['name' => 'Hobby', 'icon' => 'bi-brush'];
    public const CATEGORY_ELECTRONICS = ['name' => 'Electronics', 'icon' => 'bi-laptop'];
    public const CATEGORY_BOOKS = ['name' => 'Books', 'icon' => 'bi-book'];
    public const CATEGORY_GAMES = ['name' => 'Games', 'icon' => 'bi-controller'];
    public const CATEGORY_FASHION = ['name' => 'Fashion', 'icon' => 'bi-bag-heart'];
    public const CATEGORY_HOME = ['name' => 'Home & Garden', 'icon' => 'bi-house-heart'];
    public const CATEGORY_SPORTS = ['name' => 'Sports & Outdoors', 'icon' => 'bi-bicycle'];
    public const CATEGORY_BEAUTY = ['name' => 'Beauty & Health', 'icon' => 'bi-magic'];
    public const CATEGORY_FOOD = ['name' => 'Food & Drink', 'icon' => 'bi-cup-hot'];
    public const CATEGORY_TOYS = ['name' => 'Toys & Kids', 'icon' => 'bi-rocket-takeoff'];
    public const CATEGORY_MUSIC = ['name' => 'Music & Audio', 'icon' => 'bi-music-note-beamed'];
    public const CATEGORY_TRAVEL = ['name' => 'Travel', 'icon' => 'bi-airplane'];

    public const CATEGORIES = [
        self::CATEGORY_HOBBY,
        self::CATEGORY_ELECTRONICS,
        self::CATEGORY_BOOKS,
        self::CATEGORY_GAMES,
        self::CATEGORY_FASHION,
        self::CATEGORY_HOME,
        self::CATEGORY_SPORTS,
        self::CATEGORY_BEAUTY,
        self::CATEGORY_FOOD,
        self::CATEGORY_TOYS,
        self::CATEGORY_MUSIC,
        self::CATEGORY_TRAVEL,
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

    public function __construct(
        private TagCleanupSubscriber $tagCleanupSubscriber,
        private ParameterBagInterface $parameterBag, // Wee need that to give admin avatar
        private \App\Service\Infrastructure\FileHelper $fileHelper,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->tagCleanupSubscriber->disable();

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

        TagFactory::createMany(40); // 20 random tags
        // we add admin user
        UserFactory::createOne([
            'email' => 'admin@admin.com',
            'password' => password_hash('admin@admin.com', PASSWORD_BCRYPT),
            'categories' => $categories,
            'country' => $countries[array_rand($countries)],
            'roles' => ['ROLE_ADMIN'],
            'verified' => true,
            'nickName' => 'Admin',
            'avatar' => $this->prepareAdminAvatar(),
        ]);

        // --- UŻYTKOWNICY ---
        UserFactory::createMany(20, function () use ($countries, $categories) {
            $randomCountry = $countries[array_rand($countries)];
            $randomCategory = $categories[array_rand($categories)];

            return [
                'country' => $randomCountry,
                'categories' => [$randomCategory],
                'tags' => TagFactory::randomRange(0, 5),
            ];
        });

        WishItemFactory::createMany(100, function () use ($categories, $currencies) {
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

    private function prepareAdminAvatar(): string
    {
        // 1. Logika dla plików (Awatary)
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $sourcePath = $projectDir.'/assets/fixtures/admin.png';
        $adminAvatarFilename = 'admin.png';

        if (file_exists($sourcePath)) {
            // Wykorzystujemy Twoją nową metodę
            $this->fileHelper->uploadFromPath($sourcePath, $adminAvatarFilename);
        }

        return $adminAvatarFilename;
    }
}
