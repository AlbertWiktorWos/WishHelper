<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\WishItem;
use App\Service\TagService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * A subscriber that listens for tag changes and clears those that are no longer used.
 */
class TagCleanupSubscriber implements EventSubscriber
{
    public function __construct(
        private TagService $tagService,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->cleanupIfTagsChanged($args);
    }

    private function cleanupIfTagsChanged(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // we are only interested in User or WishItem
        if (!$entity instanceof User && !$entity instanceof WishItem) {
            return;
        }

        if ($entity->getTags() instanceof PersistentCollection && $entity->getTags()->isDirty()) {
            $this->tagService->cleanupTagsForEntity();
        }

         // nothing has changed
    }
}
