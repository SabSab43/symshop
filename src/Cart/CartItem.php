<?php

namespace App\Cart;

use App\Entity\Product;

/**
 * Item of cart
 */
class CartItem {
    
    /**
     * Product Object
     *
     * @var Product
     */
    public $product;
        
    /**
     * quantity of products
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
     * count total cart price
     *
     * @return int total
     */
    public function getTotal(): int
    {
        return $this->product->getPrice() * $this->qty;
    }

}