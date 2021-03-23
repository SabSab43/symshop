<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Security\LoginAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 *  This Controller authenticate users
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $utils, UserRepository $userRepository): Response
    {
        $form = $this->createForm(LoginType::class,["email" => $utils->getLastUsername()]);
       
        if (!empty($utils->getLastUsername())) 
        {
            $user = $userRepository->findOneBy(['email' => $utils->getLastUsername()]);
            
            if ($user != null && !$user->isVerified()) 
            {
                return $this->render('security/login.html.twig', [
                    'formView' => $form->createView(),
                    'error' => $utils->getLastAuthenticationError(),
                    "userId" => $user->getId()
                ]);
            }
        }

        return $this->render('security/login.html.twig', [
            'formView' => $form->createView(),
            'error' => $utils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/login/resend-confirmation-mail/{id}", name="security_resend_mail")
     */
    public function resendConfirmationMail(int $id, EmailVerifier $emailVerifier, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash("danger","Cet utilisateur n'existe pas.");
            return $this->redirectToRoute("security_login");
        }

        $emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@lucassaby.fr', 'Lucas Saby'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash("success", "Un nouvel email de confiramtion vous a été envoyé, pensez à vérifier vos spams.");
        return $this->redirectToRoute("security_login");
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        return new RedirectResponse('/');
    }
}
