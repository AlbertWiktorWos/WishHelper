<?php

namespace App\Dto\Response;

use Symfony\Component\Serializer\Annotation\Groups;

class WishItemView
{
    #[Groups(['wish:read'])]
    public ?int $id = null;

    #[Groups(['wish:read'])]
    public string $title;

    #[Groups(['wish:read'])]
    public ?string $description = null;

    #[Groups(['wish:read'])]
    public ?float $price = null;

    #[Groups(['wish:read'])]
    public ?CurrencyView $currency = null;

    #[Groups(['wish:read'])]
    public ?CategoryView $category = null;

    #[Groups(['wish:read'])]
    public array $tags = [];

    #[Groups(['wish:read'])]
    public ?string $link = null;

    #[Groups(['wish:read'])]
    public ?\DateTimeInterface $createdAt = null;
}
