<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    /**
     * We need this to redirect.
     */
    public function __construct(
        RouterInterface $router,
    ) {
        $this->router = $router;
    }

    /**
     * Called when onCheckPassport event was fired.
     */
    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->isVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    /**
     * call on LoginFailure event.
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }
        $response = new RedirectResponse(
            $this->router->generate('app_verify_resend_email', [
                'email' => $event->getPassport()->getUser()->getUserIdentifier(), // email
            ])
        );
        $event->setResponse($response);
    }

    /**
     * Which events we want to listen.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => [
                'onCheckPassport', // name of the event
                -10, // priority
            ],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }
}
