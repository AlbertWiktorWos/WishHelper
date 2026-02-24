<?php

namespace App\Factory;

use App\Entity\WishItem;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<WishItem>
 */
final class WishItemFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return WishItem::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'category' => CategoryFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'currency' => CurrencyFactory::new(),
            'owner' => UserFactory::new(),
            'price' => self::faker()->randomFloat(2, 0.01, 10000),
            'shared' => self::faker()->boolean(),
            'title' => self::faker()->words(3, true),
            'description' => self::faker()->realText(255),
            'tags' => [TagFactory::new()],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(WishItem $wishItem): void {})
        ;
    }
}
