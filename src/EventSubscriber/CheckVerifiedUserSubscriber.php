<?php


namespace App\EventSubscriber;


use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    /**
     * We need this to redirect
     */
    public function __construct(
        RouterInterface $router,
    )
    {
        $this->router = $router;
    }

    /**
     * Called when onCheckPassport event was fired
     * @param CheckPassportEvent $event
     */
    public function onCheckPassport(CheckPassportEvent $event)
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
     * call on LoginFailure event
     * @param LoginFailureEvent $event
     */
    public function onLoginFailure(LoginFailureEvent $event)
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }
        $response = new RedirectResponse(
            $this->router->generate('app_verify_resend_email', [
                'email' => $event->getPassport()->getUser()->getUserIdentifier() //email
            ])
        );
        $event->setResponse($response);
    }

    /**
     * Which events we want to listen
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => [
                'onCheckPassport', //name of the event
                -10 //priority
            ],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

}