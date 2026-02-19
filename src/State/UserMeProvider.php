<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use App\Service\FileHelper;
use Symfony\Bundle\SecurityBundle\Security;

class UserMeProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private FileHelper $fileHelper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->userRepository->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]) ?? null; // we fetch the User entity from the database using the email of the currently authenticated user, which is obtained from the UserInterface object returned by the getUser() method
        $user->setAvatarUrl($user->getAvatar() ? $this->fileHelper
            ->getPublicUrl($user->getAvatar()) : null);

        return $user;
    }
}
