<?php

namespace App\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CachedDictionaryCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private \Symfony\Contracts\Cache\CacheInterface $cache,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider, // the original doctrine collection provider
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider, // the original doctrine collection provider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $array = explode('\\', $operation->getClass());
        $cachePrefix = array_pop($array);

        if ($operation instanceof Get) {
            return $this->itemProvider->provide($operation, $uriVariables, $context);
        }

        $filters = $context['filters'] ?? [];
        $nonPaginationFilters = array_diff_key(
            $filters,
            array_flip(['page', 'itemsPerPage'])
        );

        if (($operation->getOrder() === ['name' => 'ASC'] || empty($operation->getOrder())) && empty($nonPaginationFilters)) {
            $cacheKey = sprintf('api_all_%s',
                strtolower($cachePrefix)
            );

            $allCurrencies = $this->cache->get($cacheKey, function ($item) use ($operation) {
                $item->expiresAfter(3600); // 1h

                return $this->entityManager->getRepository($operation->getClass())->findBy(
                    criteria: [],
                    orderBy: ['name' => 'ASC']
                );
            });

            // we create custom paginator because we don't use querybuilder from provider - we got data from cache
            $page = max(1, (int) ($filters['page'] ?? 1));
            $itemsPerPage = max(1, (int) ($filters['itemsPerPage'] ?? 20));
            $offset = ($page - 1) * $itemsPerPage;

            return new ArrayPaginator($allCurrencies, $offset, $itemsPerPage);
        }

        // if it is not default fetch then get typical provide
        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
