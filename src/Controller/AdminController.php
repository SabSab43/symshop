<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\User;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Form\UserType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\PurchaseItemRepository;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader\ProductFileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{

    private $maxForwardProduct;

    public function __construct($maxForwardProduct)
    {
        $this->maxForwardProduct = $maxForwardProduct;
    }

    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(UserRepository $userRepository, PurchaseRepository $purchaseRepository, ProductRepository $productRepository): Response
    {
        $nbUsers = $userRepository->count([]);
        $nbPurchases = $purchaseRepository->count([]);
        $nbProducts = $productRepository->count([]);

        
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'nbUsers' => $nbUsers,
            'nbPurchases' => $nbPurchases,
            'nbProducts' => $nbProducts
        ]);
    }

    /**
     * @Route("/admin/product/create", name="admin_product_create")
     */
    public function productCreate(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, ProductRepository $productRepository, ProductFileUploader $productFileUploader): Response
    {
        $product = new Product;
        $form = $this->createForm(ProductType::class, $product);       
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            if ($productRepository->findOneBy(['name' => $product->getName()]) !== null) {
                $this->addFlash("danger", "Il existe déjà un produit portant ce nom, merci de choisir un autre nom.");
                return $this->redirectToRoute("admin_product_create");
            }

            /** @var UploadedFile $mainPicture */
            $mainPicture = $form->get('mainPicture')->getData();

            if ($mainPicture) {
                $mainPictureName = $productFileUploader->upload($mainPicture);
                $product->setMainPicture($mainPictureName);
            }

            $product->setSlug(strtolower($slugger->slug($product->getName())));
            
            $em->persist($product);
            $em->flush();

            $this->addFlash("success", "Votre nouveau produit a bien été créé.");

            return $this->redirectToRoute("admin_product_create", [
                "category_slug" => $product->getCategory()->getSlug(),
                "slug" => $product->getSlug()
            ]);
        }

        $formView = $form->createView();
        return $this->render('/admin/product/create.html.twig', [
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/admin/product/edit/{id}", name="admin_product_edit")
     */
    public function productEdit(int $id, Request $request, ProductRepository $productRepository, EntityManagerInterface $em, SluggerInterface $slugger, ProductFileUploader $productFileUploader): Response
    {
        $product = $productRepository->find(['id' => $id]);   
        
        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }
        
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $mainPicture */
            $mainPicture = $form->get('mainPicture')->getData();

            if ($mainPicture !== null) {
                $newMainPicture = $productFileUploader->upload($mainPicture);
                $product->setMainPicture($newMainPicture);
            }

            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $em->flush();

            $this->addFlash("success", "Votre produit a bien été modifié.");

            return $this->redirectToRoute("admin_product_edit", [
                "category_slug" => $product->getCategory()->getSlug(),
                "slug" => $product->getSlug(),
                "id" => $product->getId()
            ]);
        }

        $formView = $form->createView();

        return $this->render('/admin/product/edit.html.twig', [
            'formView' => $formView,
            "product" =>$product
        ]);
    }

    /**
     * @Route("/admin/product/remove/{id}", name="admin_product_remove")
     */
    public function productRemove(int $id, EntityManagerInterface $em, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if (!$product) {
            $this->addFlash("danger", "Le produit que vous essayez de supprimer n'existe pas.");
            return $this->redirectToRoute("admin_product_list");
        }



        $em->remove($product);
        $em->flush();

        $this->addFlash("success", "Le produit a bien été supprimé.");

        return $this->redirectToRoute("admin_product_list");
    }   

    /**
     * @Route("/admin/product/unset-forward/{id}", name="admin_product_unsetForward")
     */
    public function productUnsetForward(int $id, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash("danger", "Vous devez sélectionner un produit vedette valide.");
            return $this->redirectToRoute("admin_product_list");
        }

        $product->setIsForward(false);
        $em->flush();

        $this->addFlash("success", "Votre produit n'est plus en avant.");

        return $this->redirectToRoute("admin_product_list");
    }

    /**
     * @Route("/admin/product/set-forward/{id}", name="admin_product_setForward")
     */
    public function productSetForward(int $id, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            $this->addFlash("danger", "Vous devez sélectionner un produit valide.");
            return $this->redirectToRoute("admin_product_list");
        }

        if ($productRepository->count(['isForward' => true]) >= $this->maxForwardProduct) {
            $this->addFlash("danger", "Il ne peut y avori que $this->maxForwardProduct produits vedettes.");
            return $this->redirectToRoute("admin_product_list");
        }

        $product->setIsForward(true);
        $em->flush();

        $this->addFlash("success", "Votre produit a bien été mis en avant.");

        return $this->redirectToRoute("admin_product_list");
    }

    /**
     * @Route("/admin/product/list", name="admin_product_list")
     */
    public function productsList(ProductRepository $productRepository) 
    {
        $notForwardProducts = $productRepository->findBy(['isForward' => false]);
        $ForwardProducts = $productRepository->findBy(['isForward' => true]);

        return $this->render("admin/product/list.html.twig", [
            "notForwardProducts" => $notForwardProducts,
            "ForwardProducts" => $ForwardProducts
        ]);
    }

    /**
     * @Route("/admin/category/create", name="admin_category_create")
     */
    public function categoryCreate(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $category = new Category;

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setSlug(strtolower($slugger->slug($category->getName())));

            $em->persist($category);
            $em->flush();

            $this->addFlash("success", "Votre nouvelle catégorie a bien été créée.");


            return $this->redirectToRoute("admin_category_create");
        }

        return $this->render('admin/category/create.html.twig', [
            "formView" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/category/edit/{id}", name="admin_category_edit")
     */
    public function categorysEdit(int $id, CategoryRepository $categoryRepository, Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {

        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas.");
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getName())));
            $em->flush();

            $this->addFlash("success", "Votre catégorie a bien été modifiée.");

            return $this->redirectToRoute("admin_category_edit", [
                "id" =>$category->getId()
            ]);
        }

        $formView = $form->createView();

        return $this->render('admin/category/edit.html.twig', [   
            "category" => $category,
            "formView" => $formView,
        ]);
    }

    /**
     * @Route("/admin/category/remove/{id}", name="admin_category_remove")
    */
    public function categoryRemove(int $id, EntityManagerInterface $em, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            $this->addFlash("danger", "La catégorie que vous essayez de supprimer n'existe pas.");
            return $this->redirectToRoute("admin_category_list");
        }

        $em->remove($category);
        $em->flush();

        $this->addFlash("success", "La catégorie a bien été supprimée.");

        return $this->redirectToRoute("admin_category_list");
    }   

    /**
     * @Route("/admin/category/list", name="admin_category_list")
     */
    public function categoriesList(CategoryRepository $categoryRepository) 
    {
        $categorys = $categoryRepository->findAll();
        return $this->render("admin/category/list.html.twig", [
            "categorys" => $categorys
        ]);
    }

    /**
     * @Route("/admin/user/list/admins", name="admin_admins_list")
    */
    public function adminList(UserRepository $userRepository) 
    {
        $admins = $userRepository->findAll();

        return $this->render("admin/user/admins_list.html.twig", [
            "admins" => $admins
        ]);
    }

    /**
     * @Route("/admin/downgrade/{id}", name="admin_downgrade_role")
    */
    public function adminDowngrade(int $id, UserRepository $userRepository, EntityManagerInterface $em) 
    {
        $admin = $userRepository->find($id);

        if (!$admin) {
            $this->addFlash("danger", "vous devez sélectionner un administrateur valide pour pouvoir le rétrograder");

            return $this->redirectToRoute("admin_admins_list");
        }

        $admin->setRoles([]);
        $em->flush();

        return $this->redirectToRoute("admin_admins_list");
    }

    /**
     * @Route("/admin/user/list/users", name="admin_user_list")
    */
    public function usersList(UserRepository $userRepository) 
    {
        $users = $userRepository->findAll();
        return $this->render("admin/user/list.html.twig", [
            "users" => $users
        ]);
    }

    /**
     * @Route("/admin/user/create", name="admin_user_create")
     */
    public function userCreate(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserType::class, new User);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User */
            $user = $form->getData();
            $requestUser = $request->get("user");
            $confirmPassword = $requestUser['confirmPassword'];

            if ($user->getPassword() != $confirmPassword) {
                $this->addFlash("danger", "Les mots de passe ne sont pas identiques.");
                return $this->redirectToRoute("admin_user_create");
            }
            
            // search an email occurence in the database
            if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash("danger", "cette adresse email est déjà liée à un compte, merci d'en choisir une autre.");
                return $this->redirectToRoute("admin_user_create");
            }

            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Votre nouvel utilisateur a bien été créé.");

            return $this->redirectToRoute("admin_user_create");
        }

        return $this->render('admin/user/create.html.twig', [
            "formView" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="admin_user_edit")
     */
    public function userEdit(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em)
    {

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur demandé n'existe pas.");
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash("success", "Votre utilisateur a bien été modifiée.");

            return $this->redirectToRoute("admin_user_edit", [
                "id" =>$user->getId()
            ]);
        }

        $formView = $form->createView();

        return $this->render('admin/user/edit.html.twig', [   
            "user" => $user,
            "formView" => $formView
        ]);
    }

    /**
     * @Route("/admin/user/remove/{id}", name="admin_user_remove")
    */
    public function userRemove(int $id, EntityManagerInterface $em, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash("danger", "L'utilisateur que vous essayez de supprimer n'existe pas.");
            return $this->redirectToRoute("admin_user_list");
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash("success", "L'utilisateur a bien été supprimé.");

        return $this->redirectToRoute("admin_user_list");
    } 

    /**
     * @Route("/admin/purchase/detail/{id}", name="admin_purchase_details")
    */
    public function purchaseDetails(int $id, PurchaseRepository $purchaseRepository)
    {
        $purchase = $purchaseRepository->find($id);
        $purchaseItems = $purchase->getPurchaseItems();

        return $this->render("admin/purchase/show.html.twig", [
            "purchase" => $purchase,
            "items" => $purchaseItems
        ]);
    }

    /**
     * @Route("/admin/purchase/remove/{id}", name="admin_purchase_remove")
    */
    public function purchaseRemove(int $id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em)
    {
        $purchase = $purchaseRepository->find($id);

        if (!$purchase) {
            $this->addFlash("danger", "la commande sélectionnée n'existe pas.");
            return $this->redirectToRoute("admin_purchase_list");
        }

        $em->remove($purchase);
        $em->flush();

        $this->addFlash("success", "la commande a bien été supprimée.");

        return $this->redirectToRoute("admin_purchase_list");
    }

    /**
     * @Route("/admin/purchase/list", name="admin_purchase_list")
    */
    public function purchasesList(PurchaseRepository $purchaseRepository)
    {
        $purchases = $purchaseRepository->findAll();

        return $this->render("admin/purchase/list.html.twig", [
            "purchases" => $purchases
        ]);
    }

}
