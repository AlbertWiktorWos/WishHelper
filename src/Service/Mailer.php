<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class Mailer
{
    private $mailer;
    private $twig;
    private $entrypointLookup;

    /**
     * Mailer constructor.
     * @param MailerInterface $mailer //we use it to send this email
     * @param Environment $twig it is a twig envoironment see "use"
     * @param EntrypointLookupInterface $entrypointLookup now we can put css to more than one email (because we can ask encore once per request/command)
     */
    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        EntrypointLookupInterface $entrypointLookup
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->entrypointLookup = $entrypointLookup;

    }

    public function sendEmailVerificationMessage(User $user, string $signedUrl): TemplatedEmail{

        /**
         * Prepare email to send
         */
        $email = (new TemplatedEmail()) //or just Email() if we dont use template

        ->to(new Address($user->getEmail(), $user->getNickName()))
        ->subject('Welcome to the WishHelper!') // subject of our email
            ->htmlTemplate('email/emailVerification.html.twig')
            ->textTemplate('email/emailVerification.txt.twig')
            ->context([
                'user' => $user,
                'url' => $signedUrl,
            ]);

        $this->mailer->send($email);

        return $email;
    }

}
