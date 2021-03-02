<?php

namespace App\Service\Cart;

use App\Service\Cart\CartItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * This Service handles Cart items
 */
class CartService {
    
    /**
     * sessionInterface Object
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Product Repository 
     *
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }
    
    /**
     * get cart from session
     *
     * @return array $cart
     */
    protected function getCart(): array
    {
        return $this->session->get('cart', []);
    }
    
    /**
     * Save Cart in session
     *
     * @param  array $cart
     * @return void
     */
    protected function saveCart(array $cart)
    {
        $this->session->set('cart', $cart);
    }
    
    /**
     * Delete Cart from session
     *
     * @return void
     */
    public function empty()
    {
        $this->saveCart([]);
    }
    
    /**
     * Increment an item quantity by its id in database
     *
     * @param  int $id
     * @return void
     */
    public function add(int $id)
    {                
        $cart = $this->getCart();

        if (!array_key_exists($id, $cart)) {
            $cart[$id] = 0;
        }

        $cart[$id]++;

        $this->saveCart($cart);
    }
    
    /**
     * Decrement an item quantity by its id in database
     *
     * @param  int $id 
     * @return void
     */
    public function decrement(int $id)
    {
        $cart = $this->getCart();

        if (!array_key_exists($id, $cart)) {
            return;
        }

        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }

        $cart[$id]--;

        $this->saveCart($cart);

    }
    
    /**
     * Select an item by its id and delete it from the Cart
     *
     * @param  int $id 
     * @return void
     */
    public function remove(int $id)
    {
        $cart = $this->getCart();

        unset($cart[$id]);

        $this->saveCart($cart);
    }
    
    /**
     * Return an integer value of cart total price
     *
     * @return int $total
     */
    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->getCart() as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product){continue;}

            $total += $product->getPrice() * $qty;
        }
        return $total;
    }
        
    /**
     * Return a CartItem array with any CardItem in the Purchase
     *
     * @return CardItem[] $cartItems
     */
    public function getDetailedCartItems(): array
    {
        $detailedCart = [];

        foreach ($this->getCart() as $id => $qty) {

            $product = $this->productRepository->find($id);

            if (!$product){continue;}

            $detailedCart[] = new CartItem($product, $qty);
        }
        return $detailedCart;
    }

}