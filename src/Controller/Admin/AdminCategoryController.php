<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCategoryController extends AbstractController
{
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
            $this->addFlash("danger", "La catégorie demandée n'existe pas.");
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

        return $this->render('admin/category/edit.html.twig', [   
            "category" => $category,
            "formView" => $form->createView(),
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
     * @Route("/admin/category/display/{id}", name="admin_category_display")
     */
    public function categoryDisplay(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em)
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);

        if (!$category) {
            $this->addFlash('danger' , 'La catégorie demandée n\'existe pas.');
            return $this->redirectToRoute('admin_category_list');
        }

        if ($category->getDisplayed()) {

            $category->setDisplayed(false);

            $em->flush();

            $this->addFlash('warning' , 'La catégorie est désormais inactive.');
            return $this->redirectToRoute('admin_category_list');
        }

        $category->setDisplayed(true);
        $this->addFlash('success' , 'La catégorie est désormais active.');
        
        $em->flush();

        return $this->redirectToRoute('admin_category_list');
    }

}