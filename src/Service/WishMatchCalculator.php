<?php

namespace App\Service;

use App\Entity\WishItem;

class WishMatchCalculator
{
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
}
