<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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

    public function __construct(FlashBagInterface $flashBag, UserRepository $userRepository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->flashBag =$flashBag;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->em = $em;
    }
    
    /**
     * Create a new user with form data;
     *
     * @param  FormInterface $form
     */
    public function createUser(FormInterface $form)
    {         
        /**@var User */   
        $user =$form->getData();
        $confirmPassword = $form->get('confirmPassword')->getViewData();

        if(!$this->checkPasswords($user->getPassword(), $confirmPassword))
        {
            $this->flashBag->add("danger", "Les mots de passe ne sont pas identiques.");
            return;
        }
        
        if ($this->emailExist($user->getEmail())) 
        {
            $this->flashBag->add("danger", "cette adresse email est déjà liée à un compte, merci d'en choisir une autre.");
            return;
        }

        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        $this->em->persist($user);
        $this->em->flush();

        $this->flashBag->add("success", "Votre inscription a bien été effecuée, vous pouvez désormais vous connecter à votre comtpe.");
    }

    /**
     * check if passwords are same or not
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
     * Check if an email adress exist in database.
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