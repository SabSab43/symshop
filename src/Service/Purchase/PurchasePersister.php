<?php

namespace App\Service\Purchase;

use DateTime;
use App\Entity\Purchase;
use App\Entity\PurchaseItem; 
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Use this class for persist & flush a purchase in the database
 */
class PurchasePersister
{
    
    /**
     * security
     *
     * @var Security
     */
    protected $security;
        
    /**
     * cartService
     *
     * @var CartService
     */
    protected $cartService;
        
    /**
     * em
     *
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(Security $security, CartService $cartService, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->cartService = $cartService;
        $this->em = $em;
    }
    
    /**
     * Store a purchase in the database
     *
     * @param  Purchase $purchase
     * @return void
     */
    public function storePurchase(Purchase $purchase)
    {
        $purchase->setUser($this->security->getUser());

        $this->em->persist($purchase);

        foreach ($this->cartService->getDetailedCartItems() as $cartItem) {
            $purchaseItem = new PurchaseItem;
            $purchaseItem->setPurchase($purchase)
                        ->setProduct($cartItem->product)
                        ->setProductName($cartItem->product->getName())
                        ->setProductPrice($cartItem->product->getPrice())
                        ->setQuantity($cartItem->qty)
                        ->setTotal($cartItem->getTotal())
            ;
            $this->em->persist($purchaseItem);
        }
        $this->em->flush();
    }
}