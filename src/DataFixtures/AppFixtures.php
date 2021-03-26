<?php

namespace App\DataFixtures;

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
use Psr\Log\LoggerInterface;
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
    
    /**
     * path to images fixtures directory
     *
     * @var string
     */
    protected $pathToImagesFixturesDirectory;
    
    /**
     * path to products main pictures directory
     *
     * @var string
     */
    protected $pathToProductsMainPicturesDirectory;
    
    /**
     * Number of image Fixtures in fixtures_images Repertory
     *
     * @var int
     */
    protected $nbFixturesImages;
    
    /**
     * logger
     *
     * @var LoggerInterface
     */
    protected $logger;
        
    /**
     * number of administrators
     *
     * @var int
     */
    protected $nbAdmins;

    /**
     * Admin password
     *
     * @var string
     */
    protected $adminPassword;
    
    /**
     * Number of users
     *
     * @var int
     */
    protected $nbUsers;
    
    /**
     * User password
     *
     * @var string
     */
    protected $userPassword;
    
    
    /**
     * Number of Categories
     *
     * @var int
     */
    protected $nbCategories;
    
    /**
     * number of forward products
     *
     * @var int
     */
    protected $nbForwardProducts;


    public function __construct(UserPasswordEncoderInterface $encoder, $pathToImagesFixturesDirectory, $pathToProductsMainPicturesDirectory, int $nbFixturesImages, LoggerInterface $logger, int $nbUsers, string $userPassword, int $nbAdmins, string $adminPassword, int $nbCategories, int $nbForwardProducts)
    {
        $this->encoder = $encoder;
        $this->pathToImagesFixturesDirectory = $pathToImagesFixturesDirectory;
        $this->pathToProductsMainPicturesDirectory = $pathToProductsMainPicturesDirectory;
        $this->nbFixturesImages = $nbFixturesImages;
        $this->logger = $logger;
        $this->nbAdmins = $nbAdmins;
        $this->adminPassword = $adminPassword;
        $this->nbUsers = $nbUsers;
        $this->userPassword = $userPassword;
        $this->nbCategories = $nbCategories;
        $this->nbForwardProducts = $nbForwardProducts;
    }
    
    /**
     * Execute fixtures
     *
     * @param  ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $this->logger->notice("Generating fixtures: Start...");  

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Prices($faker));
        $faker->addProvider(new Commerce($faker));
        $faker->addProvider(new PicsumPhotosProvider($faker));
        
        $admins = [];
        for ($a=0; $a < $this->nbAdmins; $a++) 
        { 
            $admin = new User;        
            $hash = $this->encoder->encodePassword($admin, $this->adminPassword);

            $admin->setEmail('admin@gmail.com')
                ->setPassword($hash)
                ->setRoles(['ROLE_ADMIN'])
                ->setLastName('Saby')
                ->setFirstName('Lucas')
                ->setIsVerified(true)
            ;        

            $admins[] = $admin;
            $manager->persist($admin);
        }

        $users = [];
        for ($u=0; $u < $this->nbUsers; $u++)
        { 
            $user = new User;            
            $hash = $this->encoder->encodePassword($user, $this->userPassword);

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
        $p = 0;
        $nbProducts = 0;

        array_map('unlink', glob($this->pathToProductsMainPicturesDirectory.'*.jpg'));

        for ($i=1; $i <= $this->nbCategories; $i++)
        { 
            $category = new Category();
            $category->setName($faker->department)
                     ->setDisplayed(true)
                     ->setDescription($faker->text(mt_rand(50,160)))
            ;
            
            $manager->persist($category);
            
            $nbForwardProductsByCategory = $this->nbForwardProducts / $this->nbCategories;            
            for ($j=0; $j < mt_rand(15,20); $j++) 
            {                
                if ($p >= $this->nbFixturesImages) {$p = 0;}
                $p++;
                $nbProducts++;

                $fixtureImage = "product-picture-($p).jpg";
                $finalMainPicture = "product-picture-$nbProducts-".uniqid().".jpg";
                
                try {
                    copy($this->pathToImagesFixturesDirectory.$fixtureImage, $this->pathToProductsMainPicturesDirectory.$finalMainPicture);
                } catch (\Throwable $th) {
                    throw $th;
                }
                
                $product = new Product();
                
                $product->setName($faker->productName)
                ->setPrice($faker->price(4000, 20000))
                ->setCategory($category)
                ->setMainPicture($finalMainPicture)
                ->setShortDescription($faker->paragraph())
                ->setIsForward(false)
                ->setIsDisplayed(true)
                ;
                               
                if ($nbForwardProductsByCategory > 0)
                {
                    $nbForwardProductsByCategory--;
                    $product->setIsForward(true)
                        ->setIsDisplayed(true)
                    ;
                }
                
                $products[] = $product;                        
                $manager->persist($product);
            }

        }        

        $nbPurchases = 0;
        for ($p=0; $p < mt_rand(20, 40); $p++)
        { 
            $nbPurchases++;

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

            foreach ($selectedProducts as $product)
            {
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

            if ($faker->boolean(90))
            {
                $purchase->setStatus(Purchase::STATUS_PAID);
            }

            $purchase->setTotal($totalPurchase);            
            $manager->persist($purchase);
        }
        $manager->flush();

        $this->logger->info("$this->nbAdmins administrator(s), $this->nbUsers user(s), $this->nbCategories categories $nbProducts product(s), $nbPurchases purchase(s) created.");   
        $this->logger->notice("Generating fixtures: End.");  
    }
}
