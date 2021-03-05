<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignInController extends AbstractController
{
    /**
     * @Route("/sign-in", name="sign_in")
     */
    public function index(Request $request, UserService $userService): Response
    {
        $form = $this->createForm(UserType::class,  new User);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        
            if ($userService->createUser($form) === false) {
                return $this->redirectToRoute("sign_in");
            }

            $this->addFlash("success", "Votre inscription a bien été effecuée, vous pouvez désormais vous connecter à votre comtpe.");
            return $this->redirectToRoute("security_login");
        }

        return $this->render('sign_in/index.html.twig', [
            'formView' => $form->createView(),
        ]);
    }
}
