<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Category;
use App\EventSubscriber\CategoryCacheInvalidationSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AbstractCachedDictionaryCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private \Symfony\Contracts\Cache\CacheInterface $cache,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider, // the original doctrine collection provider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (($operation->getOrder() === ['name' => 'ASC'] || empty($operation->getOrder())) && empty($operation->getFilters())) {
            // if you need parameters (pagination/filters) include them in the key
            $categories = $this->cache->get(CategoryCacheInvalidationSubscriber::CACHE_KEY, function ($item) {
                $item->expiresAfter(3600); // 1h

                // delegate to original provider (keeps ApiPlatform features)
                return $this->em->getRepository(Category::class)->findBy([], ['name' => 'ASC']);
            });

            // but when patching or posting we need to get attached to doctrine category
            if (isset($uriVariables['id'])) {
                $id = (int) $uriVariables['id'];

                foreach ($categories as $category) {
                    if ($category->getId() === $id) {
                        return $this->em->getReference(
                            $category::class,
                            $category->getId()
                        );
                    }
                }

                return null; // API Platform will return 404
            }
        }

        // if it is not default fetch then get typical provide
        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
