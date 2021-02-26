<?php

namespace App\Cart;

use App\Cart\CartItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Handler Cart products
 */
class CartService {
    
    /**
     * sessionInterface Object
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Repository of Product
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
     * add one unit of quantity item
     *
     * @param  int $id
     * @return void
     */
    public function add(int $id)
    {                
        $cart = $this->session->get('cart', []);

        if (array_key_exists($id, $cart)) {
            $cart[$id]++;
        }else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }
    
    /**
     * Decrement quantity of selected item
     *
     * @param  int $id 
     * @return void
     */
    public function decrement(int $id)
    {
        $cart = $this->session->get('cart', []);

        if (!array_key_exists($id, $cart)) {
            return;
        }

        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }

        $cart[$id]--;

        $this->session->set('cart', $cart);

    }
    
    /**
     * Delete selected item from Cart
     *
     * @param  int $id 
     * @return void
     */
    public function remove(int $id)
    {
        $cart = $this->session->get('cart', []);

        unset($cart[$id]);

        $this->session->set('cart', $cart);
    }
    
    /**
     * return total price of the cart
     *
     * @return int total
     */
    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->session->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product){continue;}

            $total += $product->getPrice() * $qty;
        }
        return $total;
    }
        
    /**
     * Return an array fileld with CartItem Objects
     *
     * @return array detailedCart
     */
    public function getDetailedCart(): array
    {
        $detailedCart = [];

        foreach ($this->session->get('cart', []) as $id => $qty) {

            $product = $this->productRepository->find($id);

            if (!$product){continue;}

            $detailedCart[] = new CartItem($product, $qty);
        }
        return $detailedCart;
    }

}