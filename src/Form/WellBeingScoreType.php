<?php

namespace App\Form;

use App\Entity\WellBeingScore;
use App\Entity\StressSurvey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WellBeingScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', IntegerType::class, [
                'label' => 'Score de bien-être',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100,
                    'placeholder' => 'Entrez un score entre 0 et 100'
                ]
            ])
            ->add('recommendation', TextareaType::class, [
                'label' => 'Recommandation',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Entrez les recommandations...'
                ]
            ])
            ->add('actionPlan', TextareaType::class, [
                'label' => 'Plan d\'action',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Décrivez le plan d\'action...'
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ajoutez un commentaire...'
                ]
            ])
            ->add('survey', EntityType::class, [
                'class' => StressSurvey::class,
                'choice_label' => function (StressSurvey $survey) {
                    return 'Survey #' . $survey->getId() . ' - ' . $survey->getDate()->format('d/m/Y');
                },
                'label' => 'Sondage associé',
                'attr' => ['class' => 'form-select']
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-success btn-lg px-5']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WellBeingScore::class,
        ]);
    }
}
