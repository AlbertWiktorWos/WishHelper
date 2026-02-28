<?php

namespace App\ApiPlatform;

use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;

#[AsDecorator('api_platform.serializer.context_builder')]
final class WishItemNotOwnerContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
    ) {
    }

    public function createFromRequest(
        Request $request,
        bool $normalization,
        ?array $extractedAttributes = null,
    ): array {
        $context = $this->decorated->createFromRequest(
            $request,
            $normalization,
            $extractedAttributes
        );

        if (!$normalization) {
            return $context;
        }

        if ($request->query->getBoolean('not_owner')) {
            $context['groups'][] = 'wish:match';
        }

        return $context;
    }
}
