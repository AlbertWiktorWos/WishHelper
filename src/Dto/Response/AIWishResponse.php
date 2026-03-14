<?php

namespace App\Dto\Response;

use App\Validator\IsValidCategoryName;
use App\Validator\IsValidCurrencyCode;
use Symfony\Component\Validator\Constraints as Assert;

class AIWishResponse
{
    #[Assert\NotBlank]
    public string $title;

    public ?string $description = null;

    #[Assert\Type('numeric')]
    public ?string $price = null;

    #[IsValidCurrencyCode]
    public ?string $currency = null;

    #[IsValidCategoryName]
    public ?string $category = null;

    public ?string $tags = null;
}
