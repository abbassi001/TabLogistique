<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\DocumentSupport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_fichier', null, [
                'label' => 'Nom du document',
                'attr' => ['placeholder' => 'Nom du document']
            ])
            ->add('type_document', ChoiceType::class, [
                'choices' => [
                    'Déclaration douane' => 'Déclaration douane',
                    'AWB' => 'AWB',
                    'Facture' => 'Facture',
                    'Certificat d\'origine' => 'Certificat d\'origine',
                    'Autre' => 'Autre'
                ],
                'label' => 'Type de document'
            ])
            ->add('file', FileType::class, [
                'label' => 'Document (PDF, DOC, DOCX, XLS, XLSX)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un document',
                    ]),
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document valide (PDF, DOC, DOCX, XLS, XLSX)',
                    ])
                ],
            ]);
            
        // Si le colis est déjà associé (venant de la page détail du colis)
        if ($options['data'] && $options['data']->getColis()) {
            $builder->add('colis', HiddenType::class, [
                'data' => $options['data']->getColis()->getId(),
                'mapped' => false
            ]);
        } else {
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
            'data_class' => DocumentSupport::class,
        ]);
    }
}