<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Entity\WishItem;
use App\Service\Item\CurrencyRateProvider;
use App\Service\Item\WishMatchCalculator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final class WishItemProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private readonly RequestStack $requestStack,
        private readonly WishMatchCalculator $calculator,
        private readonly Security $security,
        private readonly CurrencyRateProvider $currencyConverter,
        private string $defaultBaseCurrency,
    ) {
    }

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): array|object|null {
        $request = $this->requestStack->getCurrentRequest();

        // We get data through the original provider (with pagination and extension)
        $items = $this->collectionProvider->provide(
            $operation,
            $uriVariables,
            $context
        );

        if (!$request?->query->getBoolean('not_owner')) {
            return $items;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $items;
        }

        foreach ($items as $item) {
            if (!$item instanceof WishItem) {
                continue;
            }

            $score = $this->calculator->getMatchScore(
                $item,
                $user
            );
            $item->setMatchPercentage($score);

            if ($item->getPrice() && $item->getCurrency() && $user->getCountry()?->getCurrency() && $item->getCurrency()->getCode() !== $user->getCountry()->getCurrency()->getCode()) {
                $itemCurrency = $item->getCurrency()->getCode() ?? $this->defaultBaseCurrency;
                $converted = $this->currencyConverter->convert(
                    $item->getPrice(),
                    $itemCurrency,
                    $user->getCountry()->getCurrency()->getCode(),
                );

                $item->setPriceInfoInUserCurrency(round($converted, 2).' '.$user->getCountry()->getCurrency()->getCode());
            }
        }

        return $items;
    }
}
