<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\DocumentSupport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_fichier')
            ->add('type_document')
            ->add('date_upload', null, [
                'widget' => 'single_text'
            ])
            ->add('chemin_stockage')
            ->add('colis', EntityType::class, [
                'class' => Colis::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentSupport::class,
        ]);
    }
}
