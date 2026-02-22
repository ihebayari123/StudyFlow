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
                    'placeholder' => 'Entrez le nom du médecin',
                    'maxlength' => 50
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prénom du médecin',
                    'maxlength' => 50
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]
            ])
            
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@clinique.com',
                    'maxlength' => 180
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'email est obligatoire'
                    ]),
                    new Assert\Email([
                        'message' => 'Veuillez entrer un email valide',
                        'mode' => 'strict'
                    ]),
                    new Assert\Length([
                        'max' => 180,
                        'maxMessage' => 'L\'email ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+33 1 23 45 67 89',
                    'maxlength' => 20
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le téléphone est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 20,
                        'minMessage' => 'Le numéro de téléphone doit contenir au moins {{ limit }} chiffres',
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\d\s\-\+\(\)]+$/',
                        'message' => 'Le numéro de téléphone n\'est pas valide. Format accepté : +33 1 23 45 67 89'
                    ])
                ]
            ])
            
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'choices' => [
                    'Disponible' => true,
                    'Non disponible' => false
                ],
                'expanded' => true, // Affiche comme des boutons radio
                'multiple' => false, // Un seul choix possible
                'attr' => [
                    'class' => 'form-check'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Veuillez sélectionner une disponibilité'
                    ])
                ]
            ])
            
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary btn-submit'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
            'attr' => [
                'novalidate' => 'novalidate',
                'class' => 'needs-validation'
            ]
        ]);
    }
}