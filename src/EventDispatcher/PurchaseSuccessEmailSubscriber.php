<?php

namespace App\EventDispatcher;

use App\Event\PurchaseSuccessEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Sends an email when a purchase is successful paid
 */
class PurchaseSuccessEmailSubscriber implements EventSubscriberInterface{
    /**
     * logger
     *
     * @var LoggerInterface $logger
     */
    protected  $logger;
        
    /**
     * mailer
     *
     * @var MailerInterface
     */
    protected $mailer;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            "purchase.success" => 'sendSuccessEmail'
        ];
    }
    
    /**
     * Sends a successful order  email to the user
     *
     * @param  mixed $purchaseEvent
     * @return void
     */
    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseEvent)
    {
        $purchase = $purchaseEvent->getPurchase();
        $currentUser = $purchase->getUser();

        $email = new TemplatedEmail();
        $email->from(new Address("contact@lucassaby.fr"))
            ->to($currentUser->getEmail())
            ->subject("Confirmation de votre commande n°".$purchase->getId())
            ->text("Votre commande n°".$purchase->getId()." est bien en route.")
            ->htmlTemplate('emails/purchase_success.html.twig')
            ->context([
                'purchase' => $purchase,
                'currentUser' => $currentUser
            ])
        ;

        $this->mailer->send($email);

        $this->logger->info("Commande n°". $purchase->getId().": Email de confirmation envoyé.");
    }

}