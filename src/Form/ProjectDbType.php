<?php

namespace App\Form;

use App\Entity\ProjectDb;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;  // Ajout de l'import pour NotBlank
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // Import pour ChoiceType

class ProjectDbType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domaine', ChoiceType::class, [
                'choices' => [
                    'Web Development' => 'Web Development',    // Label => Value
                    'App Development' => 'App Development',
                    'Design' => 'Design',
                    'Video Editing' => 'Video Editing',
                ],

                'placeholder' => 'Select a domain',   // Placeholder text when no option is selected
                'attr' => [
                    'class' => 'form-select'   // Adds a Bootstrap class for styling the select box
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control custom-textarea', // Bootstrap and custom classes
                    'rows' => 6, // Control the height of the textarea
                    'placeholder' => 'Enter project description here...', // Optional placeholder
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectDb::class,
        ]);
    }
}
//class SearchProjectDbType extends AbstractType
//{
//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {
//        $builder
//            ->add('id', TextType::class, [
//                'required' => false,
//                'label' => 'Search by ID',
//            ])
//            ->add('domaine', TextType::class, [
//                'required' => false,
//                'label' => 'Search by Name',
//            ])
//            ->add('description', TextType::class, [
//                'required' => false,
//                'label' => 'Search by Name',
//            ])
//            ->add('Rechercher', SubmitType::class, [
//                'label' => 'Rechercher',
//            ]);
//    }
//}

