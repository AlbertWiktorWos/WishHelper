<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\EventSubscriber\CategoryCacheInvalidationSubscriber;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CachedCategoryCollectionProvider implements ProviderInterface
{
    public function __construct(
        private \Symfony\Contracts\Cache\CacheInterface $cache,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider, // the original doctrine collection provider
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider, // the original doctrine collection provider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            return $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        if (($operation->getOrder() === ['name' => 'ASC'] || empty($operation->getOrder())) && empty($operation->getFilters())) {
            // if you need parameters (pagination/filters) include them in the key

            $result = $this->cache->get(CategoryCacheInvalidationSubscriber::CACHE_KEY, function ($item) use ($operation, $uriVariables, $context) {
                $resultPaginator = $this->collectionProvider->provide($operation, $uriVariables, $context);
                assert($resultPaginator instanceof Paginator);

                return $resultPaginator->getQuery()->getResult();
            });

            return $result;
        }

        // if it is not default fetch then get typical provide
        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
