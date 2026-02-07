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
use Symfony\Component\Validator\Constraints as Assert;

class StressSurveyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le futur.'
                    ]),
                ]
            ])
            ->add('sleepHours', IntegerType::class, [
                'label' => 'Heures de sommeil',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 24
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nombre d\'heures de sommeil est obligatoire.'
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 24,
                        'notInRangeMessage' => 'Les heures de sommeil doivent être entre {{ min }} et {{ max }} heures.'
                    ]),
                ]
            ])
            ->add('studyHours', IntegerType::class, [
                'label' => 'Heures d\'étude',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 24
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nombre d\'heures d\'étude est obligatoire.'
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 24,
                        'notInRangeMessage' => 'Les heures d\'étude doivent être entre {{ min }} et {{ max }} heures.'
                    ]),
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function (Utilisateur $user) {
                    return $user->getNom() . ' ' . $user->getPrenom() . ' (#' . $user->getId() . ')';
                },
                'label' => 'Utilisateur',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Veuillez sélectionner un utilisateur.'
                    ]),
                ]
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StressSurvey::class,
        ]);
    }
}
