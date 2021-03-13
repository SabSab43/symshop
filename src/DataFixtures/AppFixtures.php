<?php

namespace App\DataFixtures;

use COM;
use Faker\Factory;
use App\Entity\Product;
use Liior\Faker\Prices;
use App\Entity\Category;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\User;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Fills database
 */
class AppFixtures extends Fixture
{    
    /**
     * password encoder
     *
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;


    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
    /**
     * Execute fixtures
     *
     * @param  ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Prices($faker));
        $faker->addProvider(new Commerce($faker));
        $faker->addProvider(new PicsumPhotosProvider($faker));


        $admin = new User;

        $hash = $this->encoder->encodePassword($admin, 'admin123VerySafe');

        $admin->setEmail('admin@gmail.com')
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN'])
            ->setLastName('Saby')
            ->setFirstName('Lucas')
            ->setIsVerified(true)
        ;
        
        $manager->persist($admin);

        $users = [];

        for ($u=0; $u < 5; $u++) { 
            $user = new User;
            $hash = $this->encoder->encodePassword($user, 'user123VerySafe');

            $user->setEmail("user$u@gmail.com")
                ->setPassword($hash)
                ->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setIsVerified(true)
            ;

            $users[] = $user;
            $manager->persist($user);
        }

        $products = [];

        $p=0;
        $pmax=23;  //Numbers of images products available in ../public/uploads/images/products

       

        for ($i=1; $i <= 3; $i++) { 
            $category = new Category();
            $category->setName($faker->department)
                     ->setDisplayed(true)
                     ->setDescription($faker->text(mt_rand(50,160)))
            ;

            $manager->persist($category);

            $isForward = 1;
            for ($j=0; $j < mt_rand(15,20); $j++) { 

                if ($p >= $pmax) {
                    $p=1;
                } else {
                    $p++;
                }
                
                $product = new Product();
                $product->setName($faker->productName)
                        ->setPrice($faker->price(4000, 20000))
                        ->setCategory($category)
                        ->setMainPicture("product-picture-($p).jpg")
                        ->setShortDescription($faker->paragraph())
                        ->setIsForward(false)
                ;
                // set one forward product by category
                if ($isForward === 1) {
                    $isForward--;
                    $product->setIsForward(true);
                }

                $products[] = $product;                        
                $manager->persist($product);
            }
        }        

        for ($p=0; $p < mt_rand(20, 40); $p++) { 
            $purchase = new Purchase;
            $totalPurchase =0;
            $purchase->setFullname($faker->name())
                    ->setAddress($faker->streetAddress)
                    ->setPostalCode($faker->postcode)
                    ->setCity($faker->city)
                    ->setUser($faker->randomElement($users))
                    ->setPurchasedAt($faker->dateTimeBetween('-6 months', 'now'))
            ;

            $selectedProducts = $faker->randomElements($products, mt_rand(3,5));

            foreach ($selectedProducts as $product) {
                $purchaseItem = new PurchaseItem;
                $purchaseItem->setProduct($product)
                             ->setQuantity(mt_rand(1,3))
                             ->setProductName($product->getName())
                             ->setProductPrice($product->getPrice())
                             ->setTotal($product->getPrice() * $purchaseItem->getQuantity())
                             ->setPurchase($purchase)
                ;
                $totalPurchase += $purchaseItem->getTotal();
                $manager->persist($purchaseItem);
            }

            if ($faker->boolean(90)) {
                $purchase->setStatus(Purchase::STATUS_PAID);
            }

            $purchase->setTotal($totalPurchase);
            
            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
