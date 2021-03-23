<?php

namespace App\Controller\Admin;

use App\Repository\ProductRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminIndexController extends AbstractController
{

    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(UserRepository $userRepository, PurchaseRepository $purchaseRepository, ProductRepository $productRepository): Response
    {
        $nbUsers = $userRepository->count([]);
        $nbPurchases = $purchaseRepository->count([]);
        $nbProducts = $productRepository->count([]);

        
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'nbUsers' => $nbUsers,
            'nbPurchases' => $nbPurchases,
            'nbProducts' => $nbProducts
        ]);
    }

}
