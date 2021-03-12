<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Service\User\UserService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    

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
    public function userCreate(Request $request, UserService $userService): Response
    {
        $form = $this->createForm(UserType::class, new User);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($userService->createUser($form) === false) {
                return $this->redirectToRoute("admin_user_create");
            }

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

        return $this->render('admin/user/edit.html.twig', [   
            "user" => $user,
            "formView" => $form->createView()
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

}