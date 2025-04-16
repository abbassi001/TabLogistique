<?php

namespace App\Form;

use App\Entity\Colis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColisBasicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('codeTracking', TextType::class, [
            //     'label' => 'Code de suivi',
            //     'attr' => [
            //         'placeholder' => 'Ex: TAB123456789',
            //         'class' => 'form-control'
            //     ]
            // ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions (L x l x h)',
                'attr' => [
                    'placeholder' => 'Ex: 50cm x 30cm x 20cm',
                    'class' => 'form-control'
                ]
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'attr' => [
                    'placeholder' => 'Ex: 5.2',
                    'class' => 'form-control'
                ]
            ])
            ->add('valeur_declaree', NumberType::class, [
                'label' => 'Valeur déclarée (€)',
                'attr' => [
                    'placeholder' => 'Ex: 250',
                    'class' => 'form-control'
                ]
            ])
            ->add('date_creation', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nature_marchandise', TextType::class, [
                'label' => 'Nature de la marchandise',
                'attr' => [
                    'placeholder' => 'Ex: Électronique, Vêtements, etc.',
                    'class' => 'form-control'
                ]
            ])
            ->add('description_marchandise', TextareaType::class, [
                'label' => 'Description de la marchandise',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée du contenu...',
                    'rows' => 3,
                    'class' => 'form-control'
                ]
            ])
            ->add('classification_douaniere', TextType::class, [
                'label' => 'Classification douanière',
                'attr' => [
                    'placeholder' => 'Ex: 8471.30',
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colis::class,
        ]);
    }
}