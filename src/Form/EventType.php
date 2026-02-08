<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre : obligatoire et commence par une majuscule
            ->add('titre', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre est obligatoire.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z]/',
                        'message' => 'Le titre doit commencer par une majuscule.'
                    ]),
                ],
            ])

            // Description : obligatoire et au moins 10 mots
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description est obligatoire.'
                    ]),
                    new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                        if (str_word_count($value) < 10) {
                            $context->buildViolation('La description doit contenir au moins 10 mots.')
                                ->addViolation();
                        }
                    }),
                ],
            ])

            // Date de création : obligatoire et >= date d'aujourd'hui
            ->add('dateCreation', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de création est obligatoire.'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être à partir d\'aujourd\'hui.'
                    ]),
                ],
            ])

            // Type : obligatoire, choix entre education, culture, bien-etre
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Education' => 'education',
                    'Culture' => 'culture',
                    'Bien-être' => 'bien-etre',
                ],
                'placeholder' => 'Sélectionnez un type',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Vous devez choisir un type.'
                    ]),
                ],
            ])

            // Image : optionnelle (pas obligatoire)
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}