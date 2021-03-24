<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Handle User creation
 */
class UserService 
{    
    /**
     * flashbag
     *
     * @var FlashBagInterface
     */
    private $flashBag;   

    /**
     * userRepository
     *
     * @var UserRepository
     */
    private $userRepository;    

    /**
     * encoder
     *
     * @var UserPasswordEncoderInterface
     */

    private $encoder;    
    
    /**
     * EntityManagerInterface
     *
     * @var EntityManagerInterface
     */
    private $em;

    private $emailVerifier;

    public function __construct(FlashBagInterface $flashBag, UserRepository $userRepository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, EmailVerifier $emailVerifier)
    {
        $this->flashBag =$flashBag;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->em = $em;
        $this->emailVerifier = $emailVerifier;
    }
        
    /**
     * create a new User and send a signed url
     *
     * @param  User $user
     * @param  FormInterface $form
     * @return void
     */
    public function createUser(User $user, FormInterface $form)
    {         
        $user->setPassword(
            $this->encoder->encodePassword(
                $user,
                $form->get('password')->getData()
            )
        );

        $this->em->persist($user);
        $this->em->flush();

        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@lucassaby.fr', 'Lucas Saby'))
                ->to($user->getEmail())
                ->subject('SymShop - Confirmation de votre adresse email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        $this->flashBag->add("success", "Votre inscription a bien été prise en comtpe, un email de confirmation vient de vous être envoyé pour confirmer votre inscription.");
    }

    /**
     * check if two submited passwords are same or not
     *
     * @param  string $password
     * @param  string $confirmPassword
     * @return bool
     */
    public function checkPasswords(string $password, string $confirmPassword): bool
    {
        return $password === $confirmPassword;
    }
    
    /**
     * Check if an email adress exists in database.
     *
     * @param  string $email
     * @return bool
     */
    public function emailExist(string $email): bool
    {
        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            return true;
        }
        return false;
    }
    
    /**
     * Set current User to Admin
     *
     * @param  mixed $user
     * @param  mixed $isConfirmed
     * @return void
     */
    public function setAdmin(User $user, bool $isConfirmed)
    {
        if ($isConfirmed)
        {
            $user->setRoles(["ROLE_ADMIN"]);
            $this->em->flush();
            $this->flashBag->add("success", "L'utilisateur est désormais administrateur.");
        }
        else
        {
            $user->setRoles(["ROLE_ADMIN"]);
            $this->flashBag->add("info", "L'utilisateur n'a pas été promu.");
        }
    }
    
    /**
     * unset currrent Admin to user 
     *
     * @param  mixed $admin
     * @param  mixed $isConfirmed
     * @return void
     */
    public function unsetAdmin(User $admin, bool $isConfirmed)
    {
        if ($isConfirmed) 
            {
                $admin->setRoles([]);
                $this->em->flush();
                $this->flashBag->add("success", "L'utilisateur a bien été rétrogradé.");
            }
        else
            {
                $this->flashBag->add("info", "L'utilisateur n'a pas  été rétrogradé.");
            }
    }
}