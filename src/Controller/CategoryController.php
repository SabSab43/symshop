<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category/create", name="category_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $category = new Category;

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getName())));
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute("homepage");
        }

        $formView = $form->createView();

        return $this->render('category/create.html.twig', [
            "formView" => $formView
        ]);
    }

    /**
     * @Route("/admin/category/{id}/edit", name="category_edit")
     */
    public function edit($id, CategoryRepository $categoryRepository, Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
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

            return $this->redirectToRoute("homepage");
        }

        $formView = $form->createView();

        return $this->render('category/edit.html.twig', [   
            "category" => $category,
            "formView" => $formView
        ]);
    }
}
