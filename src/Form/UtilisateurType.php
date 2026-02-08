<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType; // Ajoutez cette lign
class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('motDePasse', PasswordType::class, [
                'required' => false,
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password']
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Enseignant' => 'ROLE_ENSEIGNANT',
                    'Étudiant' => 'ROLE_ETUDIANT',
                ],
                'placeholder' => 'Choisir un rôle',
            ])  
            ->add('statutCompte', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'ACTIF',
                    'Inactif' => 'INACTIF',
                    'Bloqué' => 'BLOQUE',
                ],
                'placeholder' => 'Statut du compte',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'validation_groups' => ['admin'], // Utilise uniquement le groupe 'admin'
        ]);
    }
}
