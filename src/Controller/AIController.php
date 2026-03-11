<?php

namespace App\Controller;

use App\Service\Item\WishFactory;
use App\Service\Mapper\WishItemViewMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AIController extends AbstractController
{
    public function __construct(
        private WishFactory $wishFactory,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ai/wish_proposition', name: 'wish_proposition', methods: ['POST'])]
    public function generate(
        Request $request,
        #[Target('ai.limiter')] RateLimiterFactory $rateLimiter,
        WishItemViewMapper $wishItemViewMapper,
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
            $wishItem = $this->wishFactory->createByPrompt($prompt, false);
            if ($wishItem) {
                return $this->json($wishItemViewMapper->fromEntity($wishItem));
            }

            return $this->json(['error' => 'Failed to create wish'], 500);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }
}
