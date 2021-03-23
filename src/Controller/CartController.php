<?php

namespace App\Controller;

use App\Service\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * This controller handles Cart and its items
 */
class CartController extends AbstractController
{

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id":"\d+"})
     */
    public function add(int $id, Request $request)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new NotFoundHttpException("Le produit demandé n'existe pas et ne peut donc pas être ajouté.");
        }

        $this->cartService->add($id);
        $this->addFlash("success", "Le produit a bien été ajouté au panier.");

        if ($request->query->get('backToCart')) {
            return $this->redirectToRoute("cart_show");
        }

        if ($product->getCategory() === null) {
            return $this->redirectToRoute("product_withoutCategory_show", [
                "slug" => $product->getSlug()
            ]);
        }
        return $this->redirectToRoute("product_show", [
            "category_slug" => $product->getCategory()->getSlug(),
            "slug" => $product->getSlug()
        ]);

    }

    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id":"\d+"})
     */
    public function decrement(int $id)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new NotFoundHttpException("Le produit demandé n'existe pas et nepeut donc pas être ajouté.");
        }

        $this->cartService->decrement($id);

        $this->addFlash("success", "Le produit a bien été retiré du panier.");

        return $this->redirectToRoute("cart_show");
    }

    /**
     * @Route("/delete/{id}", name="cart_delete", requirements={"id": "\d+"})
     */    
    public function delete(int $id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new NotFoundHttpException("Le produit demandé n'existe pas et ne peut donc pas être supprimé.");
        }

        $this->cartService->remove($id);

        $this->addFlash('success', "Le produit a bien été supprimé du panier.");

        return $this->redirectToRoute("cart_show");
    }

    /**
     * @Route("/cart", name="cart_show")
     */    
    public function show()
    {
        $form = $this->createForm(CartConfirmationType::class);

        $detailedCart = $this->cartService->getDetailedCartItems();
        $total = $this->cartService->getTotal();

        return $this->render("cart/show.html.twig", [
            "items" => $detailedCart,
            "total" => $total,
            "confirmationForm" => $form->createView()
        ]);
    }
}
