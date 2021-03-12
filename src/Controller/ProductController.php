<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This controller handles products
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/{category_slug}/{slug}", name="product_show", priority=-1)
     */
    public function show($slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy([
            "slug" => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

     /**
     * @Route("/product/{slug}", name="product_withoutCategory_show")
     */
    public function showWithoutCategory($slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy([
            "slug" => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

     /**
     * @Route("/shop", name="product_show_all", priority=1)
     */
    public function category(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

         return $this->render('product/show_all.html.twig', [
            'products' => $products
        ]);
    }
}
