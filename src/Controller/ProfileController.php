<?php

namespace App\Controller;

use App\Dto\Request\AvatarUpload;
use App\Service\Infrastructure\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/profile.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/api/profile/avatar', name: 'profile_avatar_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function avatarUpload(
        Request $request,
        FileHelper $uploader,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        #[Target('upload.limiter')] RateLimiterFactory $rateLimiter,
    ): JsonResponse {
        $limiter = $rateLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $dto = new AvatarUpload();
        $dto->file = $request->files->get('file');

        $violations = $validator->validate($dto);

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $user = $this->getUser();

        assert($user instanceof \App\Entity\User);

        $filename = $uploader->upload($user, $dto->file, FileHelper::AVATAR_DIRECTORY);

        $user->setAvatar($filename);
        $em->flush();

        return $this->json([
            'avatarUrl' => $uploader->getPublicUrl($filename),
        ]);
    }
}
