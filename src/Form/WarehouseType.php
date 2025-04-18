<?php

namespace App\Form;

use App\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseType extends AbstractType
{
// In WarehouseType.php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nomEntreprise')  // Change 'nom' to 'nom_entreprise'
        ->add('code_ut')
        ->add('adresse_warehouse')
        ->add('description')
    ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }
}
