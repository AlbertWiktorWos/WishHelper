<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WishItemController extends AbstractController
{
    #[Route('/wishitem/mine', name: 'app_wish_item_mine')]
    public function mine(): Response
    {
        return $this->render('wishitem/mine.html.twig', [
            'controller_name' => 'WishItemController',
        ]);
    }

    #[Route('/wishitem/search', name: 'app_wish_item_search')]
    public function search(): Response
    {
        return $this->render('wishitem/search.html.twig', [
            'controller_name' => 'WishItemController',
        ]);
    }
}
