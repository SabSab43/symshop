<?php

namespace App\Controller\Purchase;

use Stripe\Stripe;
use App\Entity\Purchase;
use Stripe\PaymentIntent;
use App\Repository\PurchaseRepository;
use App\Stripe\StripeService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Check Users Payments
 */
class PurchasePaymentController extends AbstractController
{

    /**
     * @Route("/purchase/pay/{id}", name="purchase_payment_form")
     * @IsGranted("ROLE_USER")
     */
    public function showCardForm(int $id, PurchaseRepository $purchaseRepository, StripeService $stripeService)
    {
        
        
        $purchase = $purchaseRepository->find($id);

        if (
            !$purchase 
            || ($purchase && $purchase->getUser() !== $this->getUser()) 
            || ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID) 
        ) 
        {
            return $this->redirectToRoute("cart_show");
        }
        
        $intent = $stripeService->getPaymentIntent($purchase);
        
        return $this->render('purchase/payment.html.twig', [
            'clientSecret' => $intent->client_secret,
            'purchase' => $purchase,
            'publicKey' => $stripeService->getPublicKey()
        ]);
    }
}