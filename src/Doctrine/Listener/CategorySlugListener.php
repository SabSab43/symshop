<?php

namespace App\Doctrine\Listener;

use App\Entity\Category;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Create a Slug for a new Category
 */
class CategorySlugListener 
{    
    /**
     * slugger
     *
     * @var SluggerInterface
     */
    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Category $entity)
    {       
        if (!empty($entity->getName()))
        {
            $entity->setSlug(strtolower($this->slugger->slug($entity->getName())));   
        }         
    }

    public function preFlush(Category $entity)
    {       
        if (!empty($entity->getId()))
        {
            $entity->setSlug(strtolower($this->slugger->slug($entity->getName())));   
        }         
    }
} 