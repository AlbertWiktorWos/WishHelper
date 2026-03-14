<?php

namespace App\MessageHandler;

use App\Entity\WishItemRecommendation;
use App\Enum\RecommendationType;
use App\Message\CreateWishByPromptMessage;
use App\Repository\UserRepository;
use App\Service\Item\WishFactory;
use App\Service\Item\WishMatchCalculator;
use App\Service\Mapper\WishItemViewMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
// mercure
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class CreateWishByPromptMessageHandler
{
    public function __construct(
        private WishFactory $wishFactory,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private WishItemViewMapper $wishItemViewMapper,
        private SerializerInterface $serializer,
        private HubInterface $hub,
        private WishMatchCalculator $calculator,
    ) {
    }

    public function __invoke(CreateWishByPromptMessage $message): void
    {
        $user = $this->userRepository->find($message->userId);

        $wishItem = $this->wishFactory->createByPrompt($user, $message->prompt, $message->persist);
        $wishItemView = $this->wishItemViewMapper->fromEntity($wishItem);

        $recommendation = new WishItemRecommendation();
        $recommendation->setUser($user);
        $recommendation->setWishItemTitle($wishItem->getTitle());
        $recommendation->setType(RecommendationType::AI_RECOMMENDATION);
        if (!method_exists($this->serializer, 'normalize')) {
            throw new \RuntimeException('Serializer without normalize method!');
        }
        $recommendation->setWishSnapshot(
            $this->serializer->normalize($wishItemView)
        );

        $score = $this->calculator->getMatchScore($wishItem, $user);
        $recommendation->setScore($score);
        $this->em->persist($recommendation);
        $this->em->flush();

        // we publish a new mercure event
        $update = new Update(
            'user/'.$message->userId.'/wish-item-recommendations',
            json_encode([
                'id' => $recommendation->getId(),
                'type' => RecommendationType::AI_RECOMMENDATION,
                'title' => $recommendation->getWishItemTitle(),
            ])
        );

        $this->hub->publish($update);
    }
}
