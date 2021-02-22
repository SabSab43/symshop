<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BoutiqueController extends AbstractController
{
    /**
     * @Route("/boutique", name="boutique")
     */
    public function index(): Response
    {
        return $this->render('boutique/index.html.twig', [
            'controller_name' => 'BoutiqueController',
            'title' => 'Boutique'
        ]);
    }
}
