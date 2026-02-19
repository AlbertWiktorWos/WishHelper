<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function index(#[CurrentUser] User $user = null): Response
    {
        if ($user) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('landing.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
