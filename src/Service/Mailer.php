<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\WishItemRecommendation;
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

    public function sendEmailWishNotificationMessage(User $user, WishItemRecommendation $wishItemRecommendation): TemplatedEmail
    {
        $wishItem = $wishItemRecommendation->getWishItem();

        /**
         * Prepare email to send.
         */
        $email = (new TemplatedEmail()) // or just Email() if we dont use template
        ->to(new Address($user->getEmail(), $user->getNickName()))
        ->subject('Welcome to the WishHelper!') // subject of our email
            ->htmlTemplate('email/wishRecommendation.html.twig')
            ->context([
                'user' => $user,
                'title' => $wishItemRecommendation->getWishItemTitle(),
                'description' => $wishItem->getDescription(),
                'category' => $wishItem->getCategory()->getName(),
                'tags' => implode(', ',
                    array_map(
                        fn ($tag) => $tag->getName(),
                        $wishItem->getTags()->toArray()
                    )
                ),
            ]);

        $this->mailer->send($email);

        return $email;
    }
}
