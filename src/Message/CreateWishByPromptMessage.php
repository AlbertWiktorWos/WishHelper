<?php

namespace App\Message;

final class CreateWishByPromptMessage
{
    public function __construct(
        public int $userId,
        public readonly string $prompt,
        public readonly bool $persist = false,
    ) {
    }
}
