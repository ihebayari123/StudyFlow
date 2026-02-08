<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\TypeCategorie;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;//ta3 boutton


class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('image')
            ->add('typeCategorie', EntityType::class, [
                'class' => TypeCategorie::class,
                'choice_label' => 'nomCategorie',
            ])
            ->add('user', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'role',
            ])
            ->add('save',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
