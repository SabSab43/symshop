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
    private $flashbag;   

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
        $this->flashbag =$flashBag;
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;
        $this->em = $em;
    }
    
    /**
     * Return true if user is valid and create it, return false if user is not valid
     *
     * @param  FormInterface $form
     * @return bool
     */
    public function createUser(FormInterface $form)
    {         
        /**@var User */   
        $user =$form->getData();
        $confirmPassword = $form->get('confirmPassword')->getViewData();

        if(!$this->checkPasswords($user->getPassword(), $confirmPassword))
        {
            $this->flashbag->add("danger", "Les mots de passe ne sont pas identiques.");
            return false;
        }
        
        if ($this->emailExist($user->getEmail())) 
        {
            $this->flashbag->add("danger", "cette adresse email est déjà liée à un compte, merci d'en choisir une autre.");
            return false;
        }

        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        $this->em->persist($user);
        $this->em->flush();

        return true;
    }
    
    /**
     * check if passwords are same or not
     *
     * @param  string $password
     * @param  string $confirmPassword
     * @return bool
     */
    private function checkPasswords(string $password, string $confirmPassword): bool
    {
        return $password === $confirmPassword;
    }
    
    /**
     * Check if an other user usethis email or not.
     *
     * @param  string $email
     * @return bool
     */
    private function emailExist(string $email): bool
    {
        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            return true;
        }
        return false;
    }
}