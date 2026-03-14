<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\WishItemRecommendation;
use App\Enum\RecommendationType;
use App\Event\WishItemSharedEvent;
use App\Service\Infrastructure\Mailer;
use App\Service\Item\WishMatchCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class WishItemSharedListener implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private WishMatchCalculator $calculator,
        private Mailer $mailer,
        private HubInterface $hub,
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

        $mercureUpdates = [];
        // iterating through users without loading all of them into memory
        foreach ($qb->getQuery()->toIterable() as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $score = $this->calculator->getMatchScore($wishItem, $user);

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
                ->setType(RecommendationType::SHARED_WISH)
                ->setWishItemTitle($wishItem->getTitle());

            $this->em->persist($rec);

            if ($user->isNotify()) {
                $this->mailer->sendEmailWishNotificationMessage($user, $rec);
                $rec->setNotifiedAt(new \DateTimeImmutable());
            }

            // we publish a new mercure event
            $mercureUpdates[] = new Update(
                'user/'.$user->getId().'/wish-item-recommendations', // Dodaj "wish-item-"
                json_encode([
                    'id' => $rec->getId(),
                    'type' => RecommendationType::SHARED_WISH,
                    'title' => $rec->getWishItemTitle(), // Dodaj title, bo JS go oczekuje!
                ])
            );

            // flush after each batchSize
            if ((++$i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear(); // we free up memory
            }
        }
        foreach ($mercureUpdates as $mercureUpdate) {
            $this->hub->publish($mercureUpdate);
        }

        $this->em->flush();
        $this->em->clear();
    }
}
