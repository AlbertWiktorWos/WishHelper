<?php

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\WishItemRecommendation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class MineWishItemRecommendationsExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    /*
     * Applies to collection queries - QueryCollectionExtensionInterface
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->addIsPublishedWhere($resourceClass, $queryBuilder);
    }

    /*
     * Applies to single item queries - QueryItemExtensionInterface
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addIsPublishedWhere($resourceClass, $queryBuilder);
    }

    private function addIsPublishedWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if (WishItemRecommendation::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $user = $this->security->getUser();
        // allow published items or items owned by current user
        $queryBuilder
            ->andWhere(sprintf(
                '%s.user = :owner',
                $rootAlias,
            ))
            ->setParameter('owner', $user);
    }
}
