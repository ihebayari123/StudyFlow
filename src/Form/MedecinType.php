<?php

namespace App\Form;

use App\Entity\Medecin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class MedecinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: BEN ALI',
                    'maxlength' => 100,
                    'style' => 'text-transform: uppercase;',
                ],
                'help' => 'Sera automatiquement converti en majuscules',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length([
                        'min' => 2, 'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces et tirets',
                    ]),
                ],
            ])

            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: AHMED',
                    'maxlength' => 100,
                    'style' => 'text-transform: uppercase;',
                ],
                'help' => 'Sera automatiquement converti en majuscules',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
                    new Assert\Length([
                        'min' => 2, 'max' => 100,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres, espaces et tirets',
                    ]),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@gmail.com',
                    'maxlength' => 180,
                ],
                'help' => "L'email doit obligatoirement se terminer par @gmail.com",
                'constraints' => [
                    new Assert\NotBlank(['message' => "L'email est obligatoire"]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9._%+\-]+@gmail\.com$/',
                        'message' => "L'email doit se terminer par @gmail.com",
                    ]),
                ],
            ])

            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+21612345678',
                    'maxlength' => 20,
                ],
                'help' => 'Format : +XXX suivi de 8 chiffres (ex: +21612345678)',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le téléphone est obligatoire']),
                    new Assert\Regex([
                        'pattern' => '/^\+\d{1,4}\d{8}$/',
                        'message' => 'Format requis : +XXX suivi de 8 chiffres (ex: +21612345678)',
                    ]),
                ],
            ])

            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'choices' => [
                    'Disponible' => true,
                    'Indisponible' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'form-check'],
                'label_attr' => ['class' => 'form-check-label'],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Veuillez sélectionner une disponibilité']),
                ],
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary btn-submit'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }
}
