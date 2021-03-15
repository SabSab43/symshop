<?php

namespace App\Service\Remover;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class RemoverService
{
    private $em;
    private $flashBag;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flashBag)
    {
        $this->em = $em;
        $this->flashBag = $flashBag;
    }
    

     /**
     * Remove an entity
     *
     * @param  bool $isConfirmed
     * @param  mixed $entity
     * @return void
     */
    public function Remove(bool $isConfirmed, $entity, string $name)
    {
        if ($isConfirmed) 
            {
                $this->em->remove($entity);
                $this->em->flush();
                $this->flashBag->add("success", $this->setFlashes($name, $isConfirmed));
                return;
            }
        $this->flashBag->add("info", $this->setFlashes($name, $isConfirmed));
    }

    /**
     * Set Flashes messages 
     *
     * @param string $name
     * @param bool $isConfirmed
     * @return string
     */
    public function setFlashes(string $name, bool $isConfirmed)
    {
        if ($isConfirmed) {
            switch ($name)
            {
                case "produit":
                    return "Le $name a bien été supprimé.";
                    break;
                case "utilisateur":
                    return "L'$name a bien été supprimé.";
                    break;
                case "catégorie":
                    return "La $name a bien été supprimée.";
                    break;
            }
        }
        switch ($name)
            {
                case "produit":
                    return "Le $name n'a pas été supprimé.";
                    break;
                case "utilisateur":
                    return "L'$name n'a pas été supprimé.";
                    break;
                case "catégorie":
                    return "La $name n'a pas été supprimée.";
                    break;
            }
    }
}