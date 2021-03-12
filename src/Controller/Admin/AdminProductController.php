<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\Product\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdminProductController extends AbstractController
{
    
    private $maxForwardProduct;

    public function __construct($maxForwardProduct)
    {
        $this->maxForwardProduct = $maxForwardProduct;
    }
    
    /**
     * @Route("/admin/product/create", name="admin_product_create")
     */
    public function productCreate(Request $request, ProductService $productService): Response
    {
        $product = new Product;
        $form = $this->createForm(ProductType::class, $product);     

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            if ($productService->productExist($product)) {
                $this->addFlash("danger", "Il existe déjà un produit portant ce nom, merci de choisir un autre nom.");
                return $this->redirectToRoute("admin_product_create");
            }
            
            $productService->createProduct($product, $form);

            $this->addFlash("success", "Votre nouveau produit a bien été créé.");
        }

        return $this->render('/admin/product/create.html.twig', [
            'formView' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/product/edit/{id}", name="admin_product_edit")
     */
    public function productEdit(int $id, ProductRepository $productRepository, ProductService $productService, Request $request): Response
    {
        /** @var Product */
        $product = $productRepository->find(['id' => $id]);   

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }

        $form = $this->createForm(ProductType::class, $product);     
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productService->updateProduct($product, $form);

            $this->addFlash("success", "Votre produit a bien été modifié.");

            return $this->redirectToRoute("admin_product_edit", [
                "category_slug" => $product->getCategory()->getSlug(),
                "slug" => $product->getSlug(),
                "id" => $product->getId()
            ]);

        }
        return $this->render('/admin/product/edit.html.twig', [
            'formView' => $form->createView(),
            "product" =>$product
        ]);
    }

    /**
     * @Route("/admin/product/remove/{id}", name="admin_product_remove")
     */
    public function productRemove(int $id, EntityManagerInterface $em, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash("danger", "Le produit que vous essayez de supprimer n'existe pas.");
            return $this->redirectToRoute("admin_product_list");
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash("success", "Le produit a bien été supprimé.");

        return $this->redirectToRoute("admin_product_list");
    }   

    /**
     * @Route("/admin/product/unset-forward/{id}", name="admin_product_unsetForward")
     */
    public function productUnsetForward(int $id, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash("danger", "Vous devez sélectionner un produit vedette valide.");
            return $this->redirectToRoute("admin_product_list");
        }

        $product->setIsForward(false);
        $em->flush();

        $this->addFlash("success", "Votre produit n'est plus en avant.");

        return $this->redirectToRoute("admin_product_list");
    }

    /**
     * @Route("/admin/product/set-forward/{id}", name="admin_product_setForward")
     */
    public function productSetForward(int $id, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash("danger", "Vous devez sélectionner un produit valide.");
            return $this->redirectToRoute("admin_product_list");
        }

        if ($productRepository->count(['isForward' => true]) >= $this->maxForwardProduct) {
            $this->addFlash("danger", "Il ne peut y avori que $this->maxForwardProduct produits vedettes.");
            return $this->redirectToRoute("admin_product_list");
        }

        $product->setIsForward(true);
        $em->flush();

        $this->addFlash("success", "Votre produit a bien été mis en avant.");

        return $this->redirectToRoute("admin_product_list");
    }

    /**
     * @Route("/admin/product/list", name="admin_product_list")
     */
    public function productsList(ProductRepository $productRepository) 
    {
        $notForwardProducts = $productRepository->findBy(['isForward' => false]);
        $ForwardProducts = $productRepository->findBy(['isForward' => true]);

        return $this->render("admin/product/list.html.twig", [
            "notForwardProducts" => $notForwardProducts,
            "ForwardProducts" => $ForwardProducts
        ]);
    }

}