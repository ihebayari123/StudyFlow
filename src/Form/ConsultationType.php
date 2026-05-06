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
use Symfony\Component\Validator\Constraints as Assert;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_de_consultation', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Consultation',
                'required' => true,
                'help' => 'La date doit obligatoirement être dans le futur',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de consultation est obligatoire']),
                    new Assert\GreaterThan([
                        'value' => 'now',
                        'message' => 'La date de consultation doit être dans le futur',
                    ]),
                ],
            ])

            ->add('motif', TextType::class, [
                'label' => 'Motif',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez le motif (5 à 255 caractères)',
                    'minlength' => 5,
                    'maxlength' => 255,
                ],
                'help' => '5 à 255 caractères',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le motif est obligatoire']),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le motif doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le motif ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'required' => true,
                'choices' => [
                    '-- Sélectionner --' => '',
                    'Homme'    => 'Homme',
                    'Femme'    => 'Femme',
                    'Etudiant' => 'Etudiant',
                ],
                'help' => 'Choisir exactement : Homme, Femme ou Etudiant',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le genre est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['Homme', 'Femme', 'Etudiant'],
                        'message' => 'Le genre doit être exactement "Homme", "Femme" ou "Etudiant"',
                    ]),
                ],
            ])

            ->add('niveau', TextType::class, [
                'label' => "Niveau d'étude",
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Licence 3',
                    'minlength' => 2,
                    'maxlength' => 30,
                ],
                'help' => '2 à 30 caractères',
                'constraints' => [
                    new Assert\NotBlank(['message' => "Le niveau d'étude est obligatoire"]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => "Le niveau d'étude doit contenir au moins {{ limit }} caractères",
                        'maxMessage' => "Le niveau d'étude ne peut pas dépasser {{ limit }} caractères",
                    ]),
                ],
            ])

            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => function (Medecin $medecin) {
                    return $medecin->getNom() . ' ' . $medecin->getPrenom();
                },
                'label' => 'Médecin',
                'required' => true,
                'placeholder' => '-- Sélectionner un médecin --',
                'help' => 'Le médecin doit exister en base de données',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le médecin est obligatoire']),
                ],
            ])

            ->add('stress_survey', EntityType::class, [
                'class' => StressSurvey::class,
                'choice_label' => function (StressSurvey $survey) {
                    return 'Survey #' . $survey->getId() . ' - ' . ($survey->getDate() ? $survey->getDate()->format('Y-m-d') : '');
                },
                'label' => 'Stress Survey',
                'required' => true,
                'placeholder' => '-- Sélectionner un stress survey --',
                'help' => 'Le sondage doit exister en base de données',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le stress survey est obligatoire']),
                ],
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
