<?php

namespace App\Tests\Service;

use App\Entity\WishItem;
use App\Factory\CategoryFactory;
use App\Factory\CountryFactory;
use App\Factory\CurrencyFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use App\Integration\RestIntegration\OpenRouter\DTO\ChatAnswerDto;
use App\Integration\RestIntegration\OpenRouter\Provider\ChatProvider;
use App\Repository\CategoryRepository;
use App\Repository\CurrencyRepository;
use App\Service\Item\WishFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishFactoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories; // Trait to use Zenstruck Foundry factories

    private EntityManagerInterface $em;
    private CategoryRepository $categoryRepository;
    private CurrencyRepository $currencyRepository;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->currencyRepository = $container->get(CurrencyRepository::class);
        $this->validator = $container->get(ValidatorInterface::class);
        $this->serializer = $container->get(SerializerInterface::class);
    }

    public function testCreateByPromptPersistsWish(): void
    {
        // Tworzymy użytkownika z kategoriami, tagami i walutą
        $currency = CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 999,
        ]);
        $country = CountryFactory::createOne([
            'code' => 'US',
            'currency' => $currency,
        ]);
        $category = CategoryFactory::createOne(['name' => 'Books']);
        $tag = TagFactory::createOne(['name' => 'romantic']);
        $user = UserFactory::createOne([
            'country' => $country,
            'categories' => [$category],
            'tags' => [$tag],
            'maxPrice' => 1000,
        ]);

        // Mock ChatProvider zwracający określony wynik
        $mockProvider = $this->createMock(ChatProvider::class);
        $mockProvider->method('ask')->willReturn(
            ChatAnswerDto::fromArray([
                'title' => 'Mock Wish Item',
                'description' => 'Mock description generated in tests',
                'price' => '150',
                'currency' => $currency->getCode(),
                'category' => 'Books',
            ])
        );

        // Mock Security
        $mockSecurity = $this->createMock(Security::class);
        $mockSecurity->method('getUser')->willReturn($user->_real());

        $cache = new ArrayAdapter();

        $factory = new WishFactory(
            $mockProvider,
            $this->em,
            new NullLogger(),
            $cache,
            $this->currencyRepository,
            $this->categoryRepository,
            $this->serializer,
            $this->validator
        );

        $wish = $factory->createByPrompt($user->_real(), 'something more romantic', true);

        self::assertNotEmpty($this->em->getRepository(WishItem::class)->findOneBy(['title' => 'Mock Wish Item']));
        self::assertInstanceOf(WishItem::class, $wish);
        self::assertEquals('Mock Wish Item', $wish->getTitle());
        self::assertEquals('Mock description generated in tests', $wish->getDescription());
        self::assertEquals(150.0, $wish->getPrice());
        self::assertEquals($category->_real(), $wish->getCategory());
        self::assertEquals($currency->_real(), $wish->getCurrency());
        self::assertEquals($user->getId(), $wish->getOwner()->getId());
    }

    public function testCreateByPromptDoesNotPersistWhenPersistFalse(): void
    {
        $currency = CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 999,
        ]);
        $country = CountryFactory::createOne([
            'code' => 'US',
            'currency' => $currency,
        ]);
        $category = CategoryFactory::createOne(['name' => 'Books']);
        $tag = TagFactory::createOne(['name' => 'romantic']);
        $user = UserFactory::createOne([
            'country' => $country,
            'categories' => [$category],
            'tags' => [$tag],
            'maxPrice' => 1000,
        ]);

        $mockProvider = $this->createMock(ChatProvider::class);
        $mockProvider->method('ask')->willReturn(
            ChatAnswerDto::fromArray([
                'title' => 'Mock Wish Item',
                'description' => 'Mock description',
                'price' => '100',
                'currency' => 'USD',
                'category' => 'Books',
            ])
        );

        $cache = new ArrayAdapter();

        $factory = new WishFactory(
            $mockProvider,
            $this->em,
            new NullLogger(),
            $cache,
            $this->currencyRepository,
            $this->categoryRepository,
            $this->serializer,
            $this->validator
        );

        $wish = $factory->createByPrompt($user->_real(), 'test prompt', false);

        $this->assertInstanceOf(WishItem::class, $wish);
        self::assertEmpty($this->em->getRepository(WishItem::class)->findAll());
    }

    public function testCreateByPromptInvalidResponse(): void
    {
        $currency = CurrencyFactory::createOne([
            'code' => 'USD',
            'exchangeRate' => 999,
        ]);
        $country = CountryFactory::createOne([
            'code' => 'US',
            'currency' => $currency,
        ]);
        $category = CategoryFactory::createOne(['name' => 'Books']);
        $tag = TagFactory::createOne(['name' => 'romantic']);
        $user = UserFactory::createOne([
            'country' => $country,
            'categories' => [$category],
            'tags' => [$tag],
            'maxPrice' => 1000,
        ]);

        $mockProvider = $this->createMock(ChatProvider::class);
        $mockProvider->method('ask')->willReturn(
            ChatAnswerDto::fromArray([
                'title' => 'Mock Wish Item',
                'description' => 'description',
                'price' => '100',
                'currency' => 'non_exiting_currency',
                'category' => 'non_exiting_category',
            ])
        );

        $cache = new ArrayAdapter();

        $factory = new WishFactory(
            $mockProvider,
            $this->em,
            new NullLogger(),
            $cache,
            $this->currencyRepository,
            $this->categoryRepository,
            $this->serializer,
            $this->validator
        );
        $this->expectExceptionMessage('Invalid AI response');
        $wish = $factory->createByPrompt($user->_real(), 'test prompt', false);

        $this->assertInstanceOf(WishItem::class, $wish);
        self::assertEmpty($this->em->getRepository(WishItem::class)->findAll());
    }
}
