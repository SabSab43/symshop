<?php

namespace App\Controller\Admin;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminPurchaseController extends AbstractController
{
    
    /**
     * @Route("/admin/purchase/detail/{id}", name="admin_purchase_details")
    */
    public function purchaseDetails(int $id, PurchaseRepository $purchaseRepository)
    {
        $purchase = $purchaseRepository->find($id);
        $purchaseItems = $purchase->getPurchaseItems();

        return $this->render("admin/purchase/show.html.twig", [
            "purchase" => $purchase,
            "items" => $purchaseItems
        ]);
    }

    /**
     * @Route("/admin/purchase/remove/{id}", name="admin_purchase_remove")
    */
    public function purchaseRemove(int $id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em)
    {
        $purchase = $purchaseRepository->find($id);

        if (!$purchase) {
            $this->addFlash("danger", "La commande sélectionnée n'existe pas.");
            return $this->redirectToRoute("admin_purchase_list");
        }

        $em->remove($purchase);
        $em->flush();

        $this->addFlash("success", "La commande a bien été supprimée.");

        return $this->redirectToRoute("admin_purchase_list");
    }

    /**
     * @Route("/admin/purchase/list", name="admin_purchase_list")
    */
    public function purchasesList(PurchaseRepository $purchaseRepository)
    {
        $purchases = $purchaseRepository->findAll();

        return $this->render("admin/purchase/list.html.twig", [
            "purchases" => $purchases
        ]);
    }

}