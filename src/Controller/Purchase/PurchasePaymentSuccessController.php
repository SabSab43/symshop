<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PurchaseItemRepository;
use App\Repository\PurchaseRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * If purchase payment is successful
 */
class PurchasePaymentSuccessController extends AbstractController
{
    /**
     * @Route("/purchase/terminate/{id}", name="purchase_payment_success")
     * @IsGranted("ROLE_USER")
     */
    public function success(int $id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em, CartService $cartService)
    {
        $purchase =$purchaseRepository->find($id);

        if (
            !$purchase 
            || ($purchase && $purchase->getUser() !== $this->getUser()) 
            || ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID) 
        ) 
        {
            $this->addFlash("error", "La commande n'existe pas.");
            return $this->redirectToRoute("purchase_index");
        }

        $purchase->setStatus(Purchase::STATUS_PAID);
        
        $em->persist($purchase);
        $em->flush();

        $cartService->empty();

        $this->addFlash("success", "Votre commande a bien été validée.");
        return $this->redirectToRoute("purchase_index");
    }
}