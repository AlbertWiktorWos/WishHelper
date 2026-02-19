<?php

// src/Service/TagService.php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\WishItem;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    public function __construct(
        private TagRepository $tagRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Gets an existing tag or creates a new one if it doesn't exist.
     */
    public function getOrCreateTag(string $name): Tag
    {
        $tag = $this->tagRepository->findOneBy(['name' => $name]);
        if (!$tag) {
            $tag = new Tag();
            $tag->setName($name);
        }

        return $tag;
    }

    /**
     * Synchronizes the tag collection for any entity and assigns existing tags if any.
     */
    public function syncTags(User|WishItem $entity): User|WishItem
    {
        $tagRepo = $this->em->getRepository(Tag::class);
        assert($tagRepo instanceof TagRepository);
        if ($entity instanceof User) {  // aktualne tagi w DB
            $currentTags = $tagRepo->findUserTags($entity); // Tag[]
        } else {
            $currentTags = $tagRepo->findWishItemsTags($entity); // Tag[]
        }

        $newTagNames = $entity->getTags()->map(fn (Tag $t) => $t->getName())->toArray();

        // remove those that are not in the new list
        foreach ($currentTags as $tag) {
            if (!in_array($tag->getName(), $newTagNames, true)) {
                $entity->removeTag($tag);
            }
        }

        // add new tags or assign existing ones
        $entity->getTags()->clear(); // we remove all of them and then add them back to avoid dirty checking problems
        foreach ($newTagNames as $name) {
            $tag = $this->getOrCreateTag($name);
            $entity->addTag($tag);
        }

        return $entity;
    }

    public function cleanupTagsForEntity(): void
    {
        $tags = $this->tagRepository->findUnusedTags();
        foreach ($tags as $tag) {
            $this->em->remove($tag);
        }
        if ($tags) {
            $this->em->flush();
        }
    }
}
