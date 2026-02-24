<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    private $mailer;

    /**
     * Mailer constructor.
     *
     * @param MailerInterface $mailer //we use it to send this email
     */
    public function __construct(
        MailerInterface $mailer,
    ) {
        $this->mailer = $mailer;
    }

    public function sendEmailVerificationMessage(User $user, string $signedUrl): TemplatedEmail
    {
        /**
         * Prepare email to send.
         */
        $email = (new TemplatedEmail()) // or just Email() if we dont use template

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
