<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Sponsor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSponsor', TextType::class, [
                'label' => 'Nom du sponsor',
            ])
            
            ->add('type', ChoiceType::class, [
                'label' => 'Type de sponsor',
                'choices' => [
                    'Gold' => 'Gold',
                    'Silver' => 'Silver',
                    'Bronze' => 'Bronze',
                ],
                'placeholder' => 'Sélectionnez un type',
            ])
            
            ->add('montant', NumberType::class, [
                'label' => 'Montant',
            ])
            
            ->add('eventTitre', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'titre',
                'label' => 'Événement associé',
                'placeholder' => 'Sélectionnez un événement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sponsor::class,
        ]);
    }
}