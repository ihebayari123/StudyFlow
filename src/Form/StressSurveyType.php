<?php

namespace App\Form;

use App\Entity\StressSurvey;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StressSurveyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'attr' => ['class' => 'form-control']
            ])
            ->add('sleepHours', IntegerType::class, [
                'label' => 'Heures de sommeil',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 24
                ]
            ])
            ->add('studyHours', IntegerType::class, [
                'label' => 'Heures d\'étude',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 24
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function (Utilisateur $user) {
                    return $user->getNom() . ' ' . $user->getPrenom() . ' (#' . $user->getId() . ')';
                },
                'label' => 'Utilisateur',
                'attr' => ['class' => 'form-select']
            ])
            ->add('enregistrer', SubmitType::class)
             
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StressSurvey::class,
        ]);
    }
}
