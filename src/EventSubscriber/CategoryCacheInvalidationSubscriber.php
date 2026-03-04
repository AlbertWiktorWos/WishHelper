<?php

// src/EventSubscriber/CategoryCacheInvalidationSubscriber.php

namespace App\EventSubscriber;

use App\Entity\Category;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Contracts\Cache\CacheInterface;

class CategoryCacheInvalidationSubscriber implements EventSubscriber
{
    public const CACHE_KEY = 'categories.collection.v1';

    public function __construct(private CacheInterface $cache)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postUpdate, Events::postRemove];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->maybeInvalidate($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->maybeInvalidate($args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->maybeInvalidate($args);
    }

    private function maybeInvalidate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Category) {
            return;
        }
        $this->cache->delete(self::CACHE_KEY);
    }
}
