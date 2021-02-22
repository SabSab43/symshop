<?php

namespace App\DataFixtures;

use COM;
use Faker\Factory;
use App\Entity\Product;
use Liior\Faker\Prices;
use App\Entity\Category;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Prices($faker));
        $faker->addProvider(new Commerce($faker));
        $faker->addProvider(new PicsumPhotosProvider($faker));


        for ($i=0; $i < 3; $i++) { 
            $category = new Category();
            $category->setName($faker->department);
            $category->setSlug(strtolower($this->slugger->slug($category->getName())));

            $manager->persist($category);

            for ($j=0; $j < mt_rand(15,20); $j++) { 
                $product = new Product();
                $product->setName($faker->productName)
                        ->setPrice($faker->price(4000, 20000))
                        ->setSlug(strtolower($this->slugger->slug($product->getName())))
                        ->setCategory($category)
                        ->setMainPicture($faker->imageUrl(200, 200, true))
                        ->setShortDescription($faker->paragraph());
                        
                $manager->persist($product);
            }
        }        
        $manager->flush();
    }
}
