<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\WishItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Returns all tags assigned to a given user.
     *
     * @return Tag[]
     */
    public function findUserTags(User $user): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\User', 'u')     // User
            ->innerJoin('u.tags', 'userTag')        // user->tags relation
            ->where('userTag = t')                  // we only filter the tags of this relationship
            ->andWhere('u = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all tags assigned to a given request.
     *
     * @return Tag[]
     */
    public function findWishItemsTags(WishItem $wishItem): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\WishItem', 'u')   // WishItem
            ->innerJoin('u.tags', 'wishItem')        // wishItem->tags relation
            ->where('wishItem = t')                  // we only filter the tags of this relationship
            ->andWhere('u = :wishItem')
            ->setParameter('wishItem', $wishItem);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all tags that have no association with either User or WishItem.
     *
     * @return Tag[]
     */
    public function findUnusedTags(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.users', 'u')
            ->leftJoin('t.wishItems', 'w')
            ->where('u.id IS NULL')
            ->andWhere('w.id IS NULL')
            ->getQuery()
            ->getResult();
    }
}
