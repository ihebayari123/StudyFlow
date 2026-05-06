<?php

namespace App\Form;

use App\Entity\WellBeingScore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire WellBeingScore.
 * Le champ survey_id est attribué automatiquement par le contrôleur.
 */
class WellBeingScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Score : entier obligatoire 0–100 ──────────────────────────
            ->add('score', IntegerType::class, [
                'label' => 'Score de bien-être',
                'attr'  => [
                    'class'       => 'form-control',
                    'min'         => 0,
                    'max'         => 100,
                    'placeholder' => 'Valeur entre 0 et 100',
                ],
                'help' => 'Nombre entier obligatoire compris entre 0 et 100',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le score est obligatoire.',
                    ]),
                    new Assert\Type([
                        'type'    => 'integer',
                        'message' => 'Le score doit être un nombre entier.',
                    ]),
                    new Assert\Range([
                        'min'               => 0,
                        'max'               => 100,
                        'notInRangeMessage' => 'Le score doit être compris entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])

            // ── Recommandation médicale : obligatoire, 10–255 caractères ──
            ->add('recommendation', TextareaType::class, [
                'label' => 'Recommandation médicale',
                'attr'  => [
                    'class'       => 'form-control',
                    'rows'        => 3,
                    'placeholder' => 'Recommandation médicale (10 à 255 caractères)...',
                    'minlength'   => 10,
                    'maxlength'   => 255,
                ],
                'help' => 'Obligatoire — entre 10 et 255 caractères',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La recommandation médicale est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 10,
                        'max'        => 255,
                        'minMessage' => 'La recommandation doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La recommandation ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])

            // ── Plan d'action : obligatoire, 8–255 caractères ─────────────
            ->add('actionPlan', TextareaType::class, [
                'label' => "Plan d'action",
                'attr'  => [
                    'class'       => 'form-control',
                    'rows'        => 3,
                    'placeholder' => "Plan d'action (8 à 255 caractères)...",
                    'minlength'   => 8,
                    'maxlength'   => 255,
                ],
                'help' => "Obligatoire — entre 8 et 255 caractères",
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "Le plan d'action est obligatoire.",
                    ]),
                    new Assert\Length([
                        'min'        => 8,
                        'max'        => 255,
                        'minMessage' => "Le plan d'action doit contenir au moins {{ limit }} caractères.",
                        'maxMessage' => "Le plan d'action ne peut pas dépasser {{ limit }} caractères.",
                    ]),
                ],
            ])

            // ── Observations cliniques : obligatoire, 6–500 caractères ────
            ->add('comment', TextareaType::class, [
                'label' => 'Observations cliniques',
                'attr'  => [
                    'class'       => 'form-control',
                    'rows'        => 4,
                    'placeholder' => 'Observations cliniques (6 à 500 caractères)...',
                    'minlength'   => 6,
                    'maxlength'   => 500,
                ],
                'help' => 'Obligatoire — entre 6 et 500 caractères',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Les observations cliniques sont obligatoires.',
                    ]),
                    new Assert\Length([
                        'min'        => 6,
                        'max'        => 500,
                        'minMessage' => 'Les observations doivent contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Les observations ne peuvent pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])

            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => ['class' => 'btn btn-success btn-lg px-5'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WellBeingScore::class,
        ]);
    }
}
