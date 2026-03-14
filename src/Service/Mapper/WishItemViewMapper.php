<?php

namespace App\Service\Mapper;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Response\CategoryView;
use App\Dto\Response\CurrencyView;
use App\Dto\Response\TagView;
use App\Dto\Response\WishItemView;
use App\Entity\WishItem;

class WishItemViewMapper
{
    public function __construct(
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function fromEntity(WishItem $wish): WishItemView
    {
        $dto = new WishItemView();

        $dto->id = $wish->getId();
        $dto->title = $wish->getTitle();
        $dto->description = $wish->getDescription();
        $dto->price = $wish->getPrice();
        $dto->createdAt = $wish->getCreatedAt();

        if ($wish->getCurrency()) {
            $currency = new CurrencyView();
            $currency->id = $this->iriConverter->getIriFromResource($wish->getCurrency());
            $currency->code = $wish->getCurrency()->getCode();
            $currency->name = $wish->getCurrency()->getName();
            $dto->currency = $currency;
        }

        if (!$wish->getTags()->isEmpty()) {
            $dto->tags = [];
            foreach ($wish->getTags() as $tag) {
                $tagView = new TagView();
                $tagView->name = $tag->getName();
                $dto->tags[] = $tagView;
            }
        }

        if ($wish->getCategory()) {
            $category = new CategoryView();
            $category->id = $this->iriConverter->getIriFromResource($wish->getCategory());
            $category->name = $wish->getCategory()->getName();
            $category->icon = $wish->getCategory()->getIcon();
            $dto->category = $category;
        }

        return $dto;
    }
}
