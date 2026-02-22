<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\Medecin;
use App\Entity\StressSurvey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_de_consultation', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Consultation',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de consultation est obligatoire.'
                    ]),
                ],
            ])
            ->add('motif', ChoiceType::class, [
                'label' => 'Motif',
                'required' => true,
                'choices' => [
                    '-- Sélectionner --' => '',
                    'Triste' => 'Triste',
                    'Stressé' => 'Stressé',
                    'Fatigué' => 'Fatigué',
                    'Découragé' => 'Découragé',
                    'Frustré' => 'Frustré',
                    'Solitaire' => 'Solitaire',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le motif est obligatoire.'
                    ]),
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'required' => true,
                'choices' => [
                    '-- Sélectionner --' => '',
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le genre est obligatoire.'
                    ]),
                ],
            ])
            ->add('niveau', TextType::class, [
                'label' => 'Niveau',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le niveau est obligatoire.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le niveau doit contenir au moins {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => function(Medecin $medecin) {
                    return $medecin->getNom() . ' ' . $medecin->getPrenom();
                },
                'label' => 'Médecin',
                'required' => true,
                'placeholder' => '-- Sélectionner un médecin --',
                'constraints' => [
                    new NotNull([
                        'message' => 'Le médecin est obligatoire.'
                    ]),
                ],
            ])
            ->add('stress_survey', EntityType::class, [
                'class' => StressSurvey::class,
                'choice_label' => function(StressSurvey $survey) {
                    return 'Survey #' . $survey->getId() . ' - ' . ($survey->getDate() ? $survey->getDate()->format('Y-m-d') : '');
                },
                'label' => 'Stress Survey',
                'required' => true,
                'placeholder' => '-- Sélectionner un stress survey --',
                'constraints' => [
                    new NotNull([
                        'message' => 'Le stress survey est obligatoire.'
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
