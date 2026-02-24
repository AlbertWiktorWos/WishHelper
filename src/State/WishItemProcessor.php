<?php

namespace App\State;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\WishItem;
use App\Service\TagService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

// we need to decorate the existing persist processor to update the updatedAt timestamp and handle tags
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class WishItemProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private ProcessorInterface $innerProcessor,
        private TagService $tagService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof WishItem || !$this->security->getUser() instanceof User) {
            return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
        }

        assert($operation instanceof HttpOperation);

        if ('PATCH' === $operation->getMethod()) {
            // set updatedAt
            $data->setUpdatedAt(new \DateTimeImmutable('now'));
        } elseif ('POST' === $operation->getMethod()) {
            $data->setOwner($this->security->getUser());
        }

        $decodedData = json_decode($context['request']->getContent(), true);
        // checking if it applies to tags
        if (!empty($decodedData['tags'])) {
            // Tags synchronization
            $data = $this->tagService->syncTags($data);
        }

        // standard persist
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $this->tagService->cleanupTagsForEntity();

        return $result;
    }
}
