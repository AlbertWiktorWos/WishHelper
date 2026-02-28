<?php

namespace App\Normalizer;

use App\Entity\WishItemRecommendation;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * We add new group if wishItem is shared otherwise we nullify the wishItem to prevent display info about private wish.
 */
#[AsDecorator('api_platform.jsonld.normalizer.item')] // decorate the core JSON-LD item normalizer
class AddRecommendationReadGroupsNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        // if current user owns this object, extend serialization groups
        if (!$object instanceof WishItemRecommendation) {
            return $this->normalizer->normalize($object, $format, $context);
        }

        if ($object->getWishItem()?->isShared()) {
            // add extra field only for owner's view
            $context['groups'][] = 'recommendation:read';
        } else {
            $object->setWishItem(null);
        }

        // delegate actual normalization to the decorated normalizer
        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        // let the decorated normalizer decide if it supports this data
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        // forward serializer when required by decorated normalizer
        if ($this->normalizer instanceof SerializerAwareInterface) {
            $this->normalizer->setSerializer($serializer);
        }
    }
}
