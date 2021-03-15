<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
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

        if (!$product->getIsDisplayed()) {
            return $this->redirectToRoute("homepage");
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
     * @Route("/{slug}", name="product_category", priority=-1)
     */
    public function ShowByCategory($slug, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        /** @var Category */
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);    
        
        if (!$category || !$category->getDisplayed())
        {
            return $this->redirectToRoute("homepage");
        }
        $products = $productRepository->findBy(["category" => $category->getId(), 'isDisplayed' => true]);

        return $this->render('product/show_products.html.twig', [
            'slug' => $slug,
            'products' => $products,
            'category' =>$category
        ]);
    }

     /**
     * @Route("/shop", name="product_show_all", priority=1)
     */
    public function showAllProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['isDisplayed' => true]);

        return $this->render('product/show_products.html.twig', [
            'products' => $products
        ]);
    }
}
