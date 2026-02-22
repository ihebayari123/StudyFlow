<?php
// src/Form/ResetPasswordType.php

namespace App\Form;

use Gregwar\CaptchaBundle\Type\CaptchaType; // ← AJOUTE CET IMPORT
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['class' => 'form-input', 'placeholder' => 'Nouveau mot de passe']
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['class' => 'form-input', 'placeholder' => 'Confirmer le mot de passe']
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères'
                    ]),
                    new Regex([
                        'pattern' => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/",
                        'message' => 'Le mot de passe doit contenir une majuscule, une minuscule et un chiffre'
                    ])
                ]
            ])
            // ✅ AJOUT DU CAPTCHA
            ->add('captcha', CaptchaType::class, [
                'label' => 'Code de sécurité',
                'mapped' => false,
                'width' => 300,           # Doit correspondre à la config
                'height' => 100,
                'length' => 6,
                'keep_value' => true,
                'invalid_message' => 'Le code captcha est incorrect.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}