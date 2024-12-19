<?php

namespace App\Form;

use App\Entity\CommandeDb;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeDbType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'TND' // Placeholder for the prix field
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Write your description here'], // Add Bootstrap class and define row size
            ])
            ->add('technologie', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'write the framework here'], // Add Bootstrap class for styling
            ])
            ->add('datefin', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Deadline'], // Add Bootstrap class for styling
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeDb::class,
        ]);
    }
}

//class SearchCommandeDbType extends AbstractType
//{
//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {
//        $builder
//            ->add('id', TextType::class, [
//                'required' => false,
//                'label' => 'Search by ID',
//            ])
//            ->add('Technology', TextType::class, [
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