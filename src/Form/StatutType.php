<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\Employe;
use App\Entity\Statut;
use App\Enum\StatusType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_statut', EnumType::class, [
                'class' => StatusType::class,
                'choice_label' => function (StatusType $statusType) {
                    return $statusType->getLabel();
                }
            ])
            ->add('date_statut', null, [
                'widget' => 'single_text'
            ])
            ->add('localisation')
            ->add('employe', EntityType::class, [
                'class' => Employe::class,
                'choice_label' => function(Employe $employe) {
                    return $employe->getNom() . ' ' . $employe->getPrenom();
                },
                'required' => false
            ]);
            
        // On n'ajoute le champ colis que si on n'est pas dans le contexte du wizard
        if (!isset($options['wizard_mode']) || $options['wizard_mode'] !== true) {
            $builder->add('colis', EntityType::class, [
                'class' => Colis::class,
                'choice_label' => function(Colis $colis) {
                    return $colis->getCodeTracking() . ' - ' . $colis->getNatureMarchandise();
                }
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Statut::class,
            'wizard_mode' => false, // Option pour indiquer si on est dans le contexte du wizard
        ]);
    }
}