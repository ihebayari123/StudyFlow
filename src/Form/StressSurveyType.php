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
        $showUser = $options['show_user'] ?? true;

        $builder
            // ── Date : obligatoire, valide, pas dans le futur ──
            ->add('date', DateType::class, [
                'widget'      => 'single_text',
                'label'       => 'Date du sondage',
                'attr'        => [
                    'class' => 'form-control',
                    'max'   => (new \DateTime())->format('Y-m-d'),
                ],
                'help'        => 'Date obligatoire — ne peut pas être dans le futur',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date est obligatoire.',
                    ]),
                    new Assert\Type([
                        'type'    => \DateTime::class,
                        'message' => 'Veuillez entrer une date valide.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value'   => 'today',
                        'message' => 'La date ne peut pas être dans le futur.',
                    ]),
                ],
            ])

            // ── Heures de sommeil : entier 0–24 ──
            ->add('sleepHours', IntegerType::class, [
                'label'       => 'Heures de sommeil',
                'attr'        => [
                    'class'       => 'form-control',
                    'min'         => 0,
                    'max'         => 24,
                    'placeholder' => '0 – 24',
                ],
                'help'        => 'Entier entre 0 et 24',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le nombre d'heures de sommeil est obligatoire.",
                    ]),
                    new Assert\Type([
                        'type'    => 'integer',
                        'message' => 'Les heures de sommeil doivent être un nombre entier.',
                    ]),
                    new Assert\Range([
                        'min'               => 0,
                        'max'               => 24,
                        'notInRangeMessage' => 'Les heures de sommeil doivent être entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])

            // ── Heures d'étude : entier 0–24 ──
            ->add('studyHours', IntegerType::class, [
                'label'       => "Heures d'étude",
                'attr'        => [
                    'class'       => 'form-control',
                    'min'         => 0,
                    'max'         => 24,
                    'placeholder' => '0 – 24',
                ],
                'help'        => 'Entier entre 0 et 24',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le nombre d'heures d'étude est obligatoire.",
                    ]),
                    new Assert\Type([
                        'type'    => 'integer',
                        'message' => "Les heures d'étude doivent être un nombre entier.",
                    ]),
                    new Assert\Range([
                        'min'               => 0,
                        'max'               => 24,
                        'notInRangeMessage' => "Les heures d'étude doivent être entre {{ min }} et {{ max }}.",
                    ]),
                ],
            ]);

        // ── user_id : obligatoire, entier, FK vers utilisateur ──
        if ($showUser) {
            $builder->add('user', EntityType::class, [
                'class'         => Utilisateur::class,
                'choice_label'  => function (Utilisateur $u) {
                    return $u->getNom() . ' ' . $u->getPrenom() . ' (#' . $u->getId() . ')';
                },
                'label'         => 'Utilisateur',
                'placeholder'   => '-- Sélectionner un utilisateur --',
                'attr'          => ['class' => 'form-select'],
                'help'          => 'Obligatoire — doit correspondre à un utilisateur existant',
                'constraints'   => [
                    new Assert\NotNull([
                        'message' => "L'identifiant utilisateur est obligatoire et doit correspondre à un utilisateur existant.",
                    ]),
                ],
            ]);
        }

        $builder->add('enregistrer', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr'  => ['class' => 'btn btn-primary'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StressSurvey::class,
            'show_user'  => true,
        ]);
    }
}
