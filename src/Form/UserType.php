<?php

namespace App\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => "Adresse email",
            ])
            ->add('firstName', TextType::class, [
                'label' => "Nom",
            ])
            ->add('lastName', TextType::class, [
                'label' => "Prénom",
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) 
        {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user || null === $user->getId()) {
                $form->add('password', PasswordType::class, [
                    'label' => 'Choisissez un mot de passe',
                    'empty_data' => ''
                ])
                ->add('confirmPassword', PasswordType::class, [
                    'label' => 'confirmez votre mot de passe',
                    'mapped' => false,
                    'constraints' => [
                        new Length([
                            'min' => 8,
                            "max" => 255,
                            "minMessage" => "Le mot de passe doit contenir au moins {{ limit }} caractères",
                            "maxMessage" => "Le mot de passe doit contenir au maximum {{ limit }} caractères"
                        ]),
                        new NotBlank([
                            "message" => "Vous devez renseigner un mot de passe."
                        ])
                    ],
                ]);
            }

            if ($user && null !== $user->getId()) {
                $form->add('roles', ChoiceType::class, [    
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN'
                    ],
                    'expanded' => true,
                    'multiple' => true,
                    'label' => 'Rôles' 
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // 'data_class' => User::class,
        ]);
    }
}
