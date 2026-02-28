<?php

namespace App\State;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\WishItem;
use App\Event\WishItemSharedEvent;
use App\Service\TagService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// we need to decorate the existing persist processor to update the updatedAt timestamp and handle tags
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class WishItemProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private ProcessorInterface $innerProcessor,
        private TagService $tagService,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof WishItem || !$this->security->getUser() instanceof User) {
            return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
        }

        assert($operation instanceof HttpOperation);
        $isNew = false;
        if ('PATCH' === $operation->getMethod()) {
            // set updatedAt
            $data->setUpdatedAt(new \DateTimeImmutable('now'));
        } elseif ('POST' === $operation->getMethod()) {
            $data->setOwner($this->security->getUser());
            $isNew = true;
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

        // dispatch event only if shared=true
        if ($result->isShared()) {
            $this->dispatcher->dispatch(new WishItemSharedEvent($result, $isNew), WishItemSharedEvent::NAME);
        }

        return $result;
    }
}
