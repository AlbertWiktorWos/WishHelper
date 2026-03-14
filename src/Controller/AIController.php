<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\CreateWishByPromptMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AIController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/ai/wish_proposition', name: 'wish_proposition', methods: ['POST'])]
    public function generate(
        Request $request,
        #[Target('ai.limiter')] RateLimiterFactory $rateLimiter,
        MessageBusInterface $bus,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $limiter = $rateLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? null;

        if (empty($prompt)) {
            return $this->json(['error' => 'Missing "prompt" field in request'], 400);
        }

        try {
            $bus->dispatch(
                new CreateWishByPromptMessage($user->getId(), $prompt, false)
            );

            return $this->json(['success' => 'Wait for the wish!'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }
}
