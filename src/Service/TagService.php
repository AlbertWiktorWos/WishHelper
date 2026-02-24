<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\WishItem;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagService
{
    public function __construct(
        private TagRepository $tagRepository,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
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
        $currentTags = [];
        if ($entity instanceof User) {  // current tags from db
            $currentTags = $tagRepo->findUserTags($entity);
        } elseif ($entity->getId()) {
            $currentTags = $tagRepo->findWishItemsTags($entity);
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
            $errors = $this->validator->validate($tag);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException(array_reduce((array) $errors, function ($value, $error) { return $value."\n".$error; }, ''));
            }
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
