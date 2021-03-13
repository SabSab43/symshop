<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CategoryService
{

    private $categoryRepository;
    private $em;
    private $flashbag;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $em, FlashBagInterface $flashBag)
    {
        $this->categoryRepository = $categoryRepository;
        $this->em = $em;
        $this->flashbag = $flashBag;
    }

    public function editCategory(Category $category) 
    {
        if ($this->categoryRepository->findBy([ "name" => $category->getName() ])) 
        {
            $this->flashbag->add("danger", "ce nom de catégorie est déjà utilisé, merci d'en choisir un autre.");
            return;
        }
        $this->em->flush();
        $this->flashbag->add("success", "Votre catégorie a bien été modifiée.");
    }
}