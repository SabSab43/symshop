<?php

namespace App\Controller\Admin;

use App\Form\ConfirmType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Remover\RemoverService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user/list", name="admin_user_list")
    */
    public function usersList(UserRepository $userRepository) 
    {
        $users = $userRepository->findAll();
        return $this->render("admin/user/list.html.twig", [
            "users" => $users
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

        return $this->render('admin/user/edit.html.twig', [   
            "user" => $user,
            "formView" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/remove/{id}", name="admin_user_remove")
    */
    public function userRemove(int $id, EntityManagerInterface $em, UserRepository $userRepository, Request $request, RemoverService $removerService)
    {
        $user = $userRepository->find($id);

        if (!$user)
        {
            $this->addFlash("danger", "L'utilisateur que vous essayez de supprimer n'existe pas.");
            return $this->redirectToRoute("admin_user_list");
        }

        $form =$this->createForm(ConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            if ($form->get('confirm')->getData() !== null) 
            {
                $removerService->Remove($form->get('confirm')->getData(), $user, "utilisateur");
            }
            return $this->redirectToRoute("admin_user_list");
        }
        return $this->render("admin/shared/confirm.html.twig", [
            "entity" => "utilisateur",
            "confirmView" => $form->createView(),
            "path" => "admin_user_list"
        ]);
    } 

    /**
     * @Route("/admin/user/list/admins", name="admin_user_admins")
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
    public function unsetAdmin(int $id, UserRepository $userRepository, Request $request, UserService $userService) 
    {
        $admin = $userRepository->find($id);

        if (!$admin)
        {
            $this->addFlash("danger", "vous devez sélectionner un administrateur valide pour pouvoir le rétrograder");
            return $this->redirectToRoute("admin_user_admins");
        }

        $form = $this->createForm(ConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $userService->unsetAdmin($admin, $form->get('confirm')->getData());
            return $this->redirectToRoute("admin_user_admins");            
        }

        return $this->render("admin/shared/confirm.html.twig", [
            "entity" => "administrateur",
            "admin" => $admin,
            "confirmView" => $form->createView(),
            "path" => "admin_user_admins"
        ]);
    }

    /**
     * @Route("/admin/user/set-admin/{id}", name="admin_user_upgrade")
     */
    public function userSetAdmin(int $id, UserRepository $userRepository, Request $request, UserService $userService)
    {
        $user = $userRepository->find($id);

        if ($user === null)
        {
            $this->addFlash("danger", "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute("admin_user_list");
        }

        if ($user->getRoles() === "[ROLE_ADMIN]")
        {
            $this->addFlash("danger", "Cet utilisateur est déjà administrateur.");
            return $this->redirectToRoute("admin_user_list");
        }

        $form = $this->createForm(ConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $userService->setAdmin($user, $form->get('confirm')->getData());
            return $this->redirectToRoute("admin_user_list");
        }
        
        return $this->render("admin/shared/confirm.html.twig", [
            "entity" => "newAdmin",
            "user" => $user,
            "confirmView" => $form->createView(),
            "path" => "admin_user_list"
        ]);
    }

}