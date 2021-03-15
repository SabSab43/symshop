<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\Product\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Print the home page
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(ProductRepository $productRepository, ProductService $productService): Response
    {
        $nbForwardsProducts = $productRepository->count(['isForward' => true]);
        
        $forwardsProducts = $productService->HandleForwardsproducts($nbForwardsProducts, 3);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'title' => 'Accueil',
            'products' =>$forwardsProducts
        ]);
    }
}
