<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\WishItemRecommendation;
use App\Event\WishItemSharedEvent;
use App\Service\Mailer;
use App\Service\WishMatchCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WishItemSharedListener implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private WishMatchCalculator $calculator,
        private Mailer $mailer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WishItemSharedEvent::NAME => 'onWishItemShared',
        ];
    }

    public function onWishItemShared(WishItemSharedEvent $event): void
    {
        $wishItem = $event->getWishItem();
        $category = $wishItem->getCategory();
        if (!$category) {
            return; // without category there is no sense to proceed
        }

        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u')
            ->join('u.categories', 'uc') // join to user category collection
            ->where('uc.id = :categoryId') // wishItem category must be in the user collection
            ->andWhere('u.id != :ownerId') // we ignore the owners wishes
            ->setParameter('categoryId', $wishItem->getCategory()?->getId())
            ->setParameter('ownerId', $wishItem->getOwner()?->getId())
            ->distinct(); // we avoid duplicates if the user has several categories
        $batchSize = 50;
        $i = 0;

        // iterating through users without loading all of them into memory
        foreach ($qb->getQuery()->toIterable() as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $preferredCategoryIds = array_map(fn ($c) => $c->getId(), $user->getCategories()->toArray());
            $preferredTagIds = array_map(fn ($t) => $t->getId(), $user->getTags()->toArray());

            $score = $this->calculator->calculate($wishItem, $preferredCategoryIds, $preferredTagIds);

            // to low score - continue
            if ($score <= 50) {
                continue;
            }

            $existing = $this->em->getRepository(WishItemRecommendation::class)
                ->findOneBy(['wishItem' => $wishItem, 'user' => $user]);

            if ($existing) {
                if ($existing->getScore() !== $score) {
                    $existing->setScore($score);
                    $existing->setNotifiedAt(null);
                    $this->em->persist($existing);
                }
                continue;
            }

            $rec = new WishItemRecommendation();
            $rec->setWishItem($wishItem)
                ->setUser($user)
                ->setScore($score)
                ->setWishItemTitle($wishItem->getTitle());

            $this->em->persist($rec);

            if ($user->isNotify()) {
                $this->mailer->sendEmailWishNotificationMessage($user, $rec);
            }

            // flush after each batchSize
            if ((++$i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear(); // we free up memory
            }
        }

        $this->em->flush();
        $this->em->clear();
    }
}
