<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Reclamation2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Message', TextareaType::class, [
                'label' => 'Votre message',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le message ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 30,  // Increased the max length to 30
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le message ne peut pas dépasser {{ limit }} caractères.',
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Entrez votre message ici...',
                    'rows' => 5,  // Adjust the size of the textarea
                ],
            ])
            ->add('Projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'domaine',
                'placeholder' => 'Sélectionnez un projet',
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Veuillez sélectionner un projet.',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
