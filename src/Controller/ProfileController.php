<?php

namespace App\Controller;

use App\Dto\AvatarUploadDTO;
use App\Service\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    ): JsonResponse {
        $dto = new AvatarUploadDTO();
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
