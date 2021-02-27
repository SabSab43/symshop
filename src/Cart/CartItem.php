<?php

namespace App\Cart;

use App\Entity\Product;

/**
 * CarItem represents an item of the Cart
 */
class CartItem {
    
    /**
     * Product Object
     *
     * @var Product
     */
    public $product;
        
    /**
     * Quantity of this item in the cart
     *
     * @var int qty
     */
    public $qty;

    public function __construct(Product $product, int $qty)
    {
     $this->product = $product;
     $this->qty = $qty;   
    }
    
    /**
     * Count total price of this CartItem
     *
     * @return int total
     */
    public function getTotal(): int
    {
        return $this->product->getPrice() * $this->qty;
    }

}