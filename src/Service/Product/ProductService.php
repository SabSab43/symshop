<?php

namespace App\Service\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ProductService
{

    private $productRepository;
    private $slugger;
    private $em;
    private $flashBag;

    public function __construct(ProductRepository $productRepository, SluggerInterface $slugger, EntityManagerInterface $em, FlashBagInterface $flashBag)
    {
        $this->productRepository = $productRepository;
        $this->slugger = $slugger;
        $this->em = $em;
        $this->flashBag = $flashBag;
    }
    

    public function createProduct(Product $product, FormInterface $form)
    {
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
     * Update a product
     *
     * @param  Product $product
     * @param  FormInterface $form
     * @return void
     */
    public function updateProduct(Product $product, FormInterface $form)
    {
        $product->setSlug(strtolower($this->slugger->slug($product->getName())));

        $this->em->flush();        

        $this->flashBag->add("success", "Votre produit a bien été modifié.");
    }
    
    /**
     * Return all forwards products, if does not enought forwards products, it will completed with randoms products instead 
     *
     * @param  int $nbForwardsProducts
     * @param  int $maxForwards
     * @return array $forwardsProducts
     */
    public function HandleForwardsproducts(int $nbForwardsProducts, int $maxForwards)
    {
        $forwardsProducts = $this->productRepository->findBy(['isForward' => true]);

        if ($nbForwardsProducts < $maxForwards) 
        {
            $notForwardsProducts= $this->productRepository->findBy(['isForward' => false]);
            $nbNotForwardsProducts = count($notForwardsProducts);

            while ($nbForwardsProducts < $maxForwards)
            {
                $forwardsProducts[] = $notForwardsProducts[mt_rand(0, $nbNotForwardsProducts-1)];
                $nbForwardsProducts++;
            }
        }
        return $forwardsProducts;
    }

}