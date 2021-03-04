<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SignInController extends AbstractController
{
    /**
     * @Route("/sign-in", name="sign_in")
     */
    public function index(Request $request, EntityManagerInterface $em,UserRepository $userRepository, UserPasswordEncoderInterface $encoder): Response
    {

        $form = $this->createForm(UserType::class,  new User);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
           /** @var User */
            $user = $form->getData();
            $requestUser = $request->get("user");
            $confirmPassword = $requestUser['confirmPassword'];

            if ($user->getPassword() != $confirmPassword) {
                $this->addFlash("danger", "Vos mots de passe ne sont pas identiques.");
                return $this->redirectToRoute("sign_in");
            }
            
            // search an email occurence in the database
            if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash("danger", "cette adresse email est déjà liée à un compte, merci d'en choisir une autre.");
                return $this->redirectToRoute("sign_in");
            }

            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Votre inscription a bien été effecuée, vous pouvez désormais vous connecter à votre comtpe.");

            return $this->redirectToRoute("security_login");
        }

        return $this->render('sign_in/index.html.twig', [
            'formView' => $form->createView(),
        ]);
    }
}
