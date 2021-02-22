<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(EntityManagerInterface $em, ProductRepository $productRepository): Response
    {

        $products = $productRepository->findBy([], [], 3);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'title' => 'Accueil',
            'products' =>$products
        ]);
    }
}
