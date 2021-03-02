<?php

namespace App\Service\Stripe;

use Stripe\Stripe;
use App\Entity\Purchase;
use Stripe\PaymentIntent;

/**
 * handles Stripe service 
 */
class StripeService 
{        
    /**
     * Stripe secret key
     *
     * @var mixed
     */
    protected $secretKey;
    protected $publicKey;

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * create a new Stripe payment intent.
     *
     * @param  Purchase $purchase
     * @return PaymentIntent $intent
     */
    public function getPaymentIntent(Purchase $purchase)
    {
        Stripe::setApiKey($this->secretKey);

        $intent = PaymentIntent::create([
            'amount' => $purchase->getTotal(),
            'currency' => 'eur',
          ]);

        return $intent;
    }
}