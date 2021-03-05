<?php

namespace App\Service\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Service\FileUploader\ProductFileUploader;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ProductService
{

    private $productRepository;
    private $productFileUploader;
    private $slugger;
    private $em;

    public function __construct(ProductRepository $productRepository, ProductFileUploader $productFileUploader, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $this->productRepository = $productRepository;
        $this->productFileUploader = $productFileUploader;
        $this->slugger = $slugger;
        $this->em = $em;
    }
    

    public function createProduct(Product $product, FormInterface $form)
    {
        /** @var UploadedFile $mainPicture */
        $mainPicture = $form->get('mainPicture')->getData();

        if ($mainPicture) {
            $mainPictureName = $this->productFileUploader->upload($mainPicture);
            $product->setMainPicture($mainPictureName);
        }

        $product->setSlug(strtolower($this->slugger->slug($product->getName())));
        $product->setIsForward(false);

        $this->em->persist($product);
        $this->em->flush();
    }
    
    /**
     * Check if product name exist in the database
     *
     * @param  Product $product
     * @return bool
     */
    public function productExist(Product $product): bool
    {
        if ($this->productRepository->findOneBy(['name' => $product->getName()]) !== null) {
            return true;
        }
        return false;
    }

    /**
     * update a product
     *
     * @param  Product $product
     * @param  FormInterface $form
     * @return void
     */
    public function updateProduct(Product $product, FormInterface $form)
    {
        /** @var UploadedFile $mainPicture */
        $mainPicture = $form->get('mainPicture')->getData();

        if ($mainPicture !== null) {
            $newMainPicture = $this->productFileUploader->upload($mainPicture);
            $product->setMainPicture($newMainPicture);
        }

        $product->setSlug(strtolower($this->slugger->slug($product->getName())));

        $this->em->flush();        
    }

}