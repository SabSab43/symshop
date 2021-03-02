<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\FileUploader\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * This controller handles products
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/{slug}", name="product_category", priority=-1)
     */
    public function category($slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas.");
        }

        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }

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
     * @Route("/admin/product/create", name="product_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $product = new Product;
        $form = $this->createForm(ProductType::class, $product);       
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

             /** @var UploadedFile $mainPicture */
            $mainPicture = $form->get('mainPicture')->getData();

            if ($mainPicture) {
                $mainPictureName = $fileUploader->upload($mainPicture);
                $product->setMainPicture($mainPictureName);
            }

            $product->setSlug(strtolower($slugger->slug($product->getName())));
            
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute("product_show", [
                "category_slug" => $product->getCategory()->getSlug(),
                "slug" => $product->getSlug()
            ]);
        }

        $formView = $form->createView();
        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/admin/product/edit/{id}")
     */
    public function edit($id, Request $request, ProductRepository $productRepository, EntityManagerInterface $em, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $product = $productRepository->find(['id' => $id]);   
        
        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }
        
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $mainPicture */
            $mainPicture = $form->get('mainPicture')->getData();

            if ($mainPicture) {
                $mainPictureName = $fileUploader->upload($mainPicture);
                $product->setMainPicture($mainPictureName);
            }


            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $em->flush();

            return $this->redirectToRoute("product_show", [
                "category_slug" => $product->getCategory()->getSlug(),
                "slug" => $product->getSlug()
            ]);
        }

        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            'formView' => $formView
        ]);
    }
}
