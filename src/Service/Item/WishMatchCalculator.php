<?php

namespace App\Service\Item;

use App\Entity\User;
use App\Entity\WishItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WishMatchCalculator
{
    public function __construct(
        #[Autowire(service: 'cache.recommendation')]
        private readonly CacheInterface $cache,
    ) {
    }

    public function getMatchScore(
        WishItem $item,
        User $user,
    ): int {
        $categoryIds = array_map(
            fn ($c) => $c->getId(),
            $user->getCategories()->toArray()
        );

        $tagIds = array_map(
            fn ($t) => $t->getId(),
            $user->getTags()->toArray()
        );

        $maxPrice = $user->getMaxPrice() ? (float) $user->getMaxPrice() : null;

        $fingerprint = $this->buildUserFingerprint($user, $categoryIds, $tagIds, $maxPrice);

        $cacheKey = sprintf(
            'wish_score-user-%d-item-%d-%s',
            $user->getId(),
            $item->getId(),
            $fingerprint
        );

        $score = $this->cache->get($cacheKey, function (ItemInterface $cacheItem) use ($item, $categoryIds, $tagIds, $maxPrice) {
            $cacheItem->expiresAfter(600); // 10 min

            return $this->calculate($item, $categoryIds, $tagIds, $maxPrice);
        });

        return $score;
    }

    public function calculate(
        WishItem $item,
        array $selectedCategoryIds,
        array $selectedTagIds,
        ?float $maxPrice = null,
    ): int {
        $score = 0;

        // Category – 0 | 50%
        if (!empty($selectedCategoryIds)) {
            $itemCategoryId = $item->getCategory()?->getId();
            if (in_array($itemCategoryId, $selectedCategoryIds)) {
                $score = 50;
            }
        }

        // Tags – 0 to 50%
        if (!empty($selectedTagIds)) {
            $itemTagIds = array_map(
                fn ($tag) => $tag->getId(),
                $item->getTags()->toArray()
            );

            $matches = count(array_intersect($selectedTagIds, $itemTagIds));

            $tagScore = (int) round(
                (50 / count($selectedTagIds)) * $matches
            );

            $score += $tagScore;
        }

        // Penalty for exceeding the price
        if ($maxPrice && $item->getPrice()) {
            $price = (float) $item->getPrice();

            if ($price > $maxPrice) {
                $diffRatio = ($price - $maxPrice) / $maxPrice;
                $penalty = min(30, (int) ($diffRatio * 100));
                $score -= $penalty;
            }
        }

        return max(0, min(100, $score));
    }

    private function buildUserFingerprint(User $user, array $categoryIds, array $tagIds, ?float $maxPrice): string
    {
        sort($categoryIds);
        sort($tagIds);

        return md5(json_encode([
            'categories' => $categoryIds,
            'tags' => $tagIds,
            'maxPrice' => $maxPrice,
        ]));
    }
}
