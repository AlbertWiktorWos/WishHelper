<?php

namespace App\Service\Item;

use App\Dto\Response\AIWishResponse;
use App\Entity\Category;
use App\Entity\Country;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\WishItem;
use App\Integration\RestIntegration\OpenRouter\Provider\ChatProvider;
use App\Integration\RestIntegration\OpenRouter\ResponseFormatTypes;
use App\Repository\CategoryRepository;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WishFactory
{
    public function __construct(
        private ChatProvider $chatProvider,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private \Symfony\Contracts\Cache\CacheInterface $cache,
        private CurrencyRepository $currencyRepository,
        private CategoryRepository $categoryRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private string $defaultBaseCurrency = 'USD',
    ) {
    }

    public function createByPrompt(User $user, string $prompt, bool $persist = false): ?WishItem
    {
        $finalPrompt = $this->buildPrompt($prompt, $user);

        $cacheKey = 'ai_wish_'.md5($finalPrompt);

        $aiWishResponse = $this->cache->get($cacheKey, function (ItemInterface $item) use ($finalPrompt) {
            $item->expiresAfter(43200); // 12h
            $chatAnswerDto = $this->chatProvider->ask($finalPrompt, ResponseFormatTypes::JSON_RESPONSE);
            $responseContent = $chatAnswerDto->getContent();

            assert(method_exists($this->serializer, 'denormalize'));
            $aiWishResponse = $this->serializer->denormalize(
                $responseContent,
                AIWishResponse::class
            );

            $errors = $this->validator->validate($aiWishResponse);

            if (count($errors)) {
                throw new \RuntimeException('Invalid AI response');
            }

            return $aiWishResponse;
        });

        if (!$aiWishResponse instanceof AIWishResponse) {
            $this->cache->delete($cacheKey);
            throw new \Exception('Failed to establish response from AI client');
        }

        $wish = $this->buildWishEntity($aiWishResponse, $user);

        if ($persist) {
            $this->persistWish($wish);
        }

        return $wish;
    }

    private function buildPrompt(string $prompt, User $user): string
    {
        $text = '';

        $country = $user->getCountry();
        $currencyCode = $this->defaultBaseCurrency;

        if ($country instanceof Country) {
            $text .= 'User chosen country is '.$country->getName().'. ';
            $currencyCode = $country->getCurrency()?->getCode() ?? $currencyCode;
        }

        $categories = $user->getCategories()->map(
            fn (Category $c) => $c->getName()
        )->toArray();

        $categoriesText = implode(', ', $categories);
        $text .= 'User chosen categories are '.$categoriesText.'. ';

        $tags = $user->getTags()->map(
            fn (Tag $t) => $t->getName()
        )->toArray();

        if ($tags) {
            $text .= 'User chosen tags are '.implode(', ', $tags).'. ';
        }

        $text .= 'User request: '.$prompt.'. ';

        $text .= 'Based on the information above prepare idea for the wish. ';

        if ($user->getMaxPrice()) {
            $text .= sprintf(
                'The price should not be greater than %s %s. ',
                $user->getMaxPrice(),
                $currencyCode
            );
        }

        $jsonFormat = [
            'title' => 'title of the product',
            'description' => 'description of the product',
            'price' => 'approximate price of the product',
            'currency' => 'currency iso code of the price of the product',
            'category' => 'one of these categories: '.$categoriesText,
            'tags' => 'you may provide one or few tags separated by comma',
        ];

        $text .= sprintf(
            "\nReturn ONLY valid JSON in this format: %s",
            json_encode($jsonFormat, JSON_THROW_ON_ERROR)
        );

        return $text;
    }

    private function buildWishEntity(AIWishResponse $data, User $user): WishItem
    {
        $wish = new WishItem();

        $wish->setTitle($data->title);
        $wish->setDescription($data->description ?? null);

        if (!empty($data->price)) {
            $wish->setPrice((float) $data->price);
        }

        $category = $this->categoryRepository->findOneBy(['name' => ucfirst($data->category)]);
        $wish->setCategory($category);

        if ($data->tags) {
            foreach (explode(',', $data->tags) as $tag) {
                $tag = trim($tag);
                $tagEntity = new Tag();
                $tagEntity->setName($tag);
                $wish->addTag($tagEntity);
            }
        }

        $currency = $this->currencyRepository->findOneBy(['code' => $data->currency ?? $this->defaultBaseCurrency]);
        $wish->setCurrency($currency ?? null);

        $wish->setOwner($user);

        return $wish;
    }

    private function persistWish(WishItem $wish): void
    {
        $this->em->beginTransaction();

        try {
            $this->em->persist($wish);
            $this->em->flush();
            $this->em->commit();

            $this->logger->info('Wish successfully created by AI');
        } catch (\Throwable $e) {
            $this->em->rollback();

            $this->logger->error(
                sprintf('Error persisting wish: %s', $e->getMessage())
            );

            throw $e;
        }
    }
}
