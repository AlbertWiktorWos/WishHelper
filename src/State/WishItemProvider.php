<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Entity\WishItem;
use App\Service\WishMatchCalculator;
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

        $preferredCategoryIds = array_map(
            fn ($category) => $category->getId(),
            $user->getCategories()->toArray()
        );
        $preferredTagIds = array_map(
            fn ($tag) => $tag->getId(),
            $user->getTags()->toArray()
        );

        foreach ($items as $item) {
            if (!$item instanceof WishItem) {
                continue;
            }

            $score = $this->calculator->calculate(
                $item,
                $preferredCategoryIds,
                $preferredTagIds,
            );

            $item->setMatchPercentage($score);
        }

        return $items;
    }
}
