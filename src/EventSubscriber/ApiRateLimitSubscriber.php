<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Subscriber for consuming api limit requests.
 */
class ApiRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Target('api.limiter')]
        private RateLimiterFactory $apiLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $limiter = $this->apiLimiter->create($request->getClientIp());
        $limit = $limiter->consume(1);
        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }
}
