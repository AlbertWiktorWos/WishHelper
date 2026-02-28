<?php

namespace App\Event;

use App\Entity\WishItem;
use Symfony\Contracts\EventDispatcher\Event;

class WishItemSharedEvent extends Event
{
    public const NAME = 'wish_item.shared';

    public function __construct(
        private readonly WishItem $wishItem,
        private readonly bool $isNew,
    ) {
    }

    public function getWishItem(): WishItem
    {
        return $this->wishItem;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }
}
