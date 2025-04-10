<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Entity\ColisTransport;
use App\Entity\Destinataire;
use App\Entity\DocumentSupport;
use App\Entity\Expediteur;
use App\Entity\Photo;
use App\Entity\Statut;
use App\Entity\Transport;
use App\Enum\StatusType;
use App\Form\ColisBasicType;
use App\Form\DestinataireType;
use App\Form\DocumentSupportType;
use App\Form\ExpediteurType;
use App\Form\PhotoType;
use App\Form\StatutType;
use App\Form\TransportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/colis-wizard')]
final class ColisWizardController extends AbstractController
{
    private const SESSION_KEY = 'colis_wizard_data';

    // Correction : La méthode getWizardData ne doit pas s'appeler elle-même
    private function getWizardData(SessionInterface $session): array
    {
        // Récupérer les données de session existantes ou initialiser un tableau vide
        $wizardData = $session->get(self::SESSION_KEY, []);
        
        // S'assurer que les clés essentielles existent toujours
        if (!isset($wizardData['max_step'])) {
            $wizardData['max_step'] = 1;
        }
        
        if (!isset($wizardData['current_step'])) {
            $wizardData['current_step'] = 1;
        }
        
        if (!isset($wizardData['colis'])) {
            $wizardData['colis'] = [];
        }
        
        if (!isset($wizardData['expediteur'])) {
            $wizardData['expediteur'] = [];
        }
        
        if (!isset($wizardData['destinataire'])) {
            $wizardData['destinataire'] = [];
        }
        
        if (!isset($wizardData['transport'])) {
            $wizardData['transport'] = [];
        }
        
        if (!isset($wizardData['statut'])) {
            $wizardData['statut'] = [];
        }
        
        if (!isset($wizardData['photos'])) {
            $wizardData['photos'] = [];
        }
        
        if (!isset($wizardData['documents'])) {
            $wizardData['documents'] = [];
        }
        
        return $wizardData;
    }
    
    // Ajout de la méthode start manquante
    #[Route('/', name: 'app_colis_wizard_start', methods: ['GET'])]
    public function start(SessionInterface $session): Response
    {
        // Initialiser les données du wizard
        $wizardData = [
            'max_step' => 1,
            'current_step' => 1,
            'colis' => [],
            'expediteur' => [],
            'destinataire' => [],
            'transport' => [],
            'statut' => [],
            'photos' => [],
            'documents' => []
        ];
        
        $session->set(self::SESSION_KEY, $wizardData);
        
        // Rediriger vers la première étape
        return $this->redirectToRoute('app_colis_wizard_step1');
    }
    
    #[Route('/step1', name: 'app_colis_wizard_step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, SessionInterface $session): Response
    {
        // Informations de base du colis
        $wizardData = $this->getWizardData($session);
        $colis = new Colis();
        
        // Pré-remplir le formulaire si des données existent déjà
        if (!empty($wizardData['colis'])) {
            $formData = $wizardData['colis'];
            if (isset($formData['codeTracking'])) $colis->setCodeTracking($formData['codeTracking']);
            if (isset($formData['dimensions'])) $colis->setDimensions($formData['dimensions']);
            if (isset($formData['poids'])) $colis->setPoids((float)$formData['poids']);
            if (isset($formData['valeur_declaree'])) $colis->setValeurDeclaree((float)$formData['valeur_declaree']);
            if (isset($formData['date_creation'])) $colis->setDateCreation(new \DateTime($formData['date_creation']));
            if (isset($formData['nature_marchandise'])) $colis->setNatureMarchandise($formData['nature_marchandise']);
            if (isset($formData['description_marchandise'])) $colis->setDescriptionMarchandise($formData['description_marchandise']);
            if (isset($formData['classification_douaniere'])) $colis->setClassificationDouaniere($formData['classification_douaniere']);
        }
        
        $form = $this->createForm(ColisBasicType::class, $colis);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker les données du formulaire en session
            $wizardData['colis'] = [
                'codeTracking' => $colis->getCodeTracking(),
                'dimensions' => $colis->getDimensions(),
                'poids' => $colis->getPoids(),
                'valeur_declaree' => $colis->getValeurDeclaree(),
                'date_creation' => $colis->getDateCreation()->format('Y-m-d H:i:s'),
                'nature_marchandise' => $colis->getNatureMarchandise(),
                'description_marchandise' => $colis->getDescriptionMarchandise(),
                'classification_douaniere' => $colis->getClassificationDouaniere()
            ];
            
            $wizardData['current_step'] = 2;
            if ($wizardData['max_step'] < 2) {
                $wizardData['max_step'] = 2;
            }
            
            $session->set(self::SESSION_KEY, $wizardData);
            
            return $this->redirectToRoute('app_colis_wizard_step2');
        }
        
        return $this->render('colis_wizard/step1.html.twig', [
            'form' => $form,
            'current_step' => 1,
            'max_step' => $wizardData['max_step']
        ]);
    }
    
    #[Route('/step2', name: 'app_colis_wizard_step2', methods: ['GET', 'POST'])]
    public function step2(Request $request, SessionInterface $session): Response
    {
        // Vérifier que l'étape précédente a été complétée
        $wizardData = $this->getWizardData($session);
        if (empty($wizardData['colis'])) {
            return $this->redirectToRoute('app_colis_wizard_step1');
        }
        
        // Informations sur l'expéditeur
        $expediteur = new Expediteur();
        
        // Pré-remplir le formulaire si des données existent déjà
        if (!empty($wizardData['expediteur'])) {
            $formData = $wizardData['expediteur'];
            if (isset($formData['nom_entreprise_individu'])) $expediteur->setNomEntrepriseIndividu($formData['nom_entreprise_individu']);
            if (isset($formData['telephone'])) $expediteur->setTelephone($formData['telephone']);
            if (isset($formData['email'])) $expediteur->setEmail($formData['email']);
            if (isset($formData['pays'])) $expediteur->setPays($formData['pays']);
            if (isset($formData['adresse_complete'])) $expediteur->setAdresseComplete($formData['adresse_complete']);
        }
        
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker les données du formulaire en session
            $wizardData['expediteur'] = [
                'nom_entreprise_individu' => $expediteur->getNomEntrepriseIndividu(),
                'telephone' => $expediteur->getTelephone(),
                'email' => $expediteur->getEmail(),
                'pays' => $expediteur->getPays(),
                'adresse_complete' => $expediteur->getAdresseComplete()
            ];
            
            $wizardData['current_step'] = 3;
            if ($wizardData['max_step'] < 3) {
                $wizardData['max_step'] = 3;
            }
            
            $session->set(self::SESSION_KEY, $wizardData);
            
            return $this->redirectToRoute('app_colis_wizard_step3');
        }
        
        return $this->render('colis_wizard/step2.html.twig', [
            'form' => $form,
            'current_step' => 2,
            'max_step' => $wizardData['max_step']
        ]);
    }
    
    #[Route('/step3', name: 'app_colis_wizard_step3', methods: ['GET', 'POST'])]
    public function step3(Request $request, SessionInterface $session): Response
    {
        // Vérifier que l'étape précédente a été complétée
        $wizardData = $this->getWizardData($session);
        if (empty($wizardData['expediteur'])) {
            return $this->redirectToRoute('app_colis_wizard_step2');
        }
        
        // Informations sur le destinataire
        $destinataire = new Destinataire();
        
        // Pré-remplir le formulaire si des données existent déjà
        if (!empty($wizardData['destinataire'])) {
            $formData = $wizardData['destinataire'];
            if (isset($formData['nom_entreprise_individu'])) $destinataire->setNomEntrepriseIndividu($formData['nom_entreprise_individu']);
            if (isset($formData['telephone'])) $destinataire->setTelephone($formData['telephone']);
            if (isset($formData['email'])) $destinataire->setEmail($formData['email']);
            if (isset($formData['pays'])) $destinataire->setPays($formData['pays']);
            if (isset($formData['adresse_complete'])) $destinataire->setAdresseComplete($formData['adresse_complete']);
        }
        
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker les données du formulaire en session
            $wizardData['destinataire'] = [
                'nom_entreprise_individu' => $destinataire->getNomEntrepriseIndividu(),
                'telephone' => $destinataire->getTelephone(),
                'email' => $destinataire->getEmail(),
                'pays' => $destinataire->getPays(),
                'adresse_complete' => $destinataire->getAdresseComplete()
            ];
            
            $wizardData['current_step'] = 4;
            if ($wizardData['max_step'] < 4) {
                $wizardData['max_step'] = 4;
            }
            
            $session->set(self::SESSION_KEY, $wizardData);
            
            return $this->redirectToRoute('app_colis_wizard_step4');
        }
        
        return $this->render('colis_wizard/step3.html.twig', [
            'form' => $form,
            'current_step' => 3,
            'max_step' => $wizardData['max_step']
        ]);
    }
    
    #[Route('/step4', name: 'app_colis_wizard_step4', methods: ['GET', 'POST'])]
    public function step4(Request $request, SessionInterface $session): Response
    {
        // Vérifier que l'étape précédente a été complétée
        $wizardData = $this->getWizardData($session);
        if (empty($wizardData['destinataire'])) {
            return $this->redirectToRoute('app_colis_wizard_step3');
        }
        
        // Informations sur le statut initial
        $statut = new Statut();
        $statut->setTypeStatut(StatusType::RECU);
        $statut->setDateStatut(new \DateTime());
        
        // Pré-remplir le formulaire si des données existent déjà
        if (!empty($wizardData['statut'])) {
            $formData = $wizardData['statut'];
            if (isset($formData['type_statut'])) $statut->setTypeStatut(StatusType::from($formData['type_statut']));
            if (isset($formData['date_statut'])) $statut->setDateStatut(new \DateTime($formData['date_statut']));
            if (isset($formData['localisation'])) $statut->setLocalisation($formData['localisation']);
        } else {
            // Valeurs par défaut
            $statut->setLocalisation('Réception entrepôt');
        }
        
        $form = $this->createForm(StatutType::class, $statut);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker les données du formulaire en session
            $wizardData['statut'] = [
                'type_statut' => $statut->getTypeStatut()->value,
                'date_statut' => $statut->getDateStatut()->format('Y-m-d H:i:s'),
                'localisation' => $statut->getLocalisation(),
                'employe_id' => $statut->getEmploye() ? $statut->getEmploye()->getId() : null
            ];
            
            $wizardData['current_step'] = 5;
            if ($wizardData['max_step'] < 5) {
                $wizardData['max_step'] = 5;
            }
            
            $session->set(self::SESSION_KEY, $wizardData);
            
            return $this->redirectToRoute('app_colis_wizard_step5');
        }
        
        return $this->render('colis_wizard/step4.html.twig', [
            'form' => $form,
            'current_step' => 4,
            'max_step' => $wizardData['max_step']
        ]);
    }
    
    #[Route('/step5', name: 'app_colis_wizard_step5', methods: ['GET', 'POST'])]
    public function step5(Request $request, SessionInterface $session): Response
    {
        // Vérifier que l'étape précédente a été complétée
        $wizardData = $this->getWizardData($session);
        if (empty($wizardData['statut'])) {
            return $this->redirectToRoute('app_colis_wizard_step4');
        }
        
        // Informations sur le transport
        $transport = new Transport();
        
        // Pré-remplir le formulaire si des données existent déjà
        if (!empty($wizardData['transport'])) {
            $formData = $wizardData['transport'];
            if (isset($formData['type_transport'])) $transport->setTypeTransport($formData['type_transport']);
            if (isset($formData['compagnie_transport'])) $transport->setCompagnieTransport($formData['compagnie_transport']);
            if (isset($formData['numero_vol_navire'])) $transport->setNumeroVolNavire($formData['numero_vol_navire']);
            if (isset($formData['date_depart'])) $transport->setDateDepart(new \DateTime($formData['date_depart']));
            if (isset($formData['lieu_depart'])) $transport->setLieuDepart($formData['lieu_depart']);
            if (isset($formData['date_arrivee'])) $transport->setDateArrivee(new \DateTime($formData['date_arrivee']));
            if (isset($formData['lieu_arrivee'])) $transport->setLieuArrivee($formData['lieu_arrivee']);
        }
        
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Stocker les données du formulaire en session
            $wizardData['transport'] = [
                'type_transport' => $transport->getTypeTransport(),
                'compagnie_transport' => $transport->getCompagnieTransport(),
                'numero_vol_navire' => $transport->getNumeroVolNavire(),
                'date_depart' => $transport->getDateDepart() ? $transport->getDateDepart()->format('Y-m-d H:i:s') : null,
                'lieu_depart' => $transport->getLieuDepart(),
                'date_arrivee' => $transport->getDateArrivee()->format('Y-m-d H:i:s'),
                'lieu_arrivee' => $transport->getLieuArrivee()
            ];
            
            $wizardData['current_step'] = 6;
            if ($wizardData['max_step'] < 6) {
                $wizardData['max_step'] = 6;
            }
            
            $session->set(self::SESSION_KEY, $wizardData);
            
            return $this->redirectToRoute('app_colis_wizard_step6');
        }
        
        return $this->render('colis_wizard/step5.html.twig', [
            'form' => $form,
            'current_step' => 5,
            'max_step' => $wizardData['max_step']
        ]);
    }
    
    #[Route('/step6', name: 'app_colis_wizard_step6', methods: ['GET', 'POST'])]
    public function step6(Request $request, SessionInterface $session, SluggerInterface $slugger): Response
    {
        // Vérifier que l'étape précédente a été complétée
        $wizardData = $this->getWizardData($session);
        if (empty($wizardData['transport'])) {
            return $this->redirectToRoute('app_colis_wizard_step5');
        }
        
        // Gestion des photos
        $photo = new Photo();
        $photo->setDateUpload(new \DateTime());
        
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de fichier
            $photoFile = $form->get('file')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    
                    $photo->setUrlPhoto($newFilename);
                    
                    // Ajouter la photo à la liste des photos
                    $wizardData['photos'][] = [
                        'urlPhoto' => $newFilename,
                        'dateUpload' => $photo->getDateUpload()->format('Y-m-d H:i:s'),
                        'description' => $photo->getDescription()
                    ];
                    
                    $wizardData['current_step'] = 6; // Rester sur la même étape pour ajouter d'autres photos
                    $session->set(self::SESSION_KEY, $wizardData);
                    
                    $this->addFlash('success', 'Photo ajoutée avec succès. Vous pouvez en ajouter d\'autres ou passer à l\'étape suivante.');
                    
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo: ' . $e->getMessage());
                }
            } else {
                // Si pas de fichier, mais une URL de photo
                $wizardData['photos'][] = [
                    'urlPhoto' => $photo->getUrlPhoto(),
                    'dateUpload' => $photo->getDateUpload()->format('Y-m-d H:i:s'),
                    'description' => $photo->getDescription()
                ];
                
                $wizardData['current_step'] = 6;
                $session->set(self::SESSION_KEY, $wizardData);
                
                $this->addFlash('success', 'Référence photo ajoutée avec succès.');
            }
            
            return $this->redirectToRoute('app_colis_wizard_step6');
        }
        
        return $this->render('colis_wizard/step6.html.twig', [
            'form' => $form,
            'photos' => $wizardData['photos'] ?? [],
            'current_step' => 6,
            'max_step' => $wizardData['max_step'] >= 6 ? $wizardData['max_step'] : 6
        ]);
    }
    
    #[Route('/step7', name: 'app_colis_wizard_step7', methods: ['GET', 'POST'])]
    public function step7(Request $request, SessionInterface $session, SluggerInterface $slugger): Response
    {
        // Aucune vérification pour permettre de sauter l'étape des photos si non nécessaire
        $wizardData = $this->getWizardData($session);
        
        // Gestion des documents
        $document = new DocumentSupport();
        $document->setDateUpload(new \DateTime());
        
        $form = $this->createForm(DocumentSupportType::class, $document);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de fichier
            $docFile = $form->get('file')->getData();
            
            if ($docFile) {
                $originalFilename = pathinfo($docFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$docFile->guessExtension();
                
                try {
                    $docFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                    
                    $document->setCheminStockage($newFilename);
                    
                    // Ajouter le document à la liste
                    $wizardData['documents'][] = [
                        'nomFichier' => $document->getNomFichier(),
                        'typeDocument' => $document->getTypeDocument(),
                        'dateUpload' => $document->getDateUpload()->format('Y-m-d H:i:s'),
                        'cheminStockage' => $newFilename
                    ];
                    
                    $wizardData['current_step'] = 7; // Rester sur la même étape pour ajouter d'autres documents
                    if ($wizardData['max_step'] < 7) {
                        $wizardData['max_step'] = 7;
                    }
                    
                    $session->set(self::SESSION_KEY, $wizardData);
                    
                    $this->addFlash('success', 'Document ajouté avec succès. Vous pouvez en ajouter d\'autres ou passer à l\'étape suivante.');
                    
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du document: ' . $e->getMessage());
                }
            } else {
                // Si pas de fichier, mais des informations de document
                $wizardData['documents'][] = [
                    'nomFichier' => $document->getNomFichier(),
                    'typeDocument' => $document->getTypeDocument(),
                    'dateUpload' => $document->getDateUpload()->format('Y-m-d H:i:s'),
                    'cheminStockage' => $document->getCheminStockage()
                ];
                
                $wizardData['current_step'] = 7;
                if ($wizardData['max_step'] < 7) {
                    $wizardData['max_step'] = 7;
                }
                
                $session->set(self::SESSION_KEY, $wizardData);
                
                $this->addFlash('success', 'Document ajouté avec succès.');
            }
            
            return $this->redirectToRoute('app_colis_wizard_step7');
        }
        
        return $this->render('colis_wizard/step7.html.twig', [
            'form' => $form,
            'documents' => $wizardData['documents'] ?? [],
            'current_step' => 7,
            'max_step' => $wizardData['max_step'] >= 7 ? $wizardData['max_step'] : 7
        ]);
    }
    
    #[Route('/review', name: 'app_colis_wizard_review', methods: ['GET'])]
    public function review(SessionInterface $session): Response
    {
        $wizardData = $this->getWizardData($session);
        
        // S'assurer qu'au moins les informations essentielles sont présentes
        if (empty($wizardData['colis']) || empty($wizardData['destinataire'])) {
            return $this->redirectToRoute('app_colis_wizard_start');
        }
        
        $wizardData['current_step'] = 8;
        if ($wizardData['max_step'] < 8) {
            $wizardData['max_step'] = 8;
        }
        $session->set(self::SESSION_KEY, $wizardData);
        
        return $this->render('colis_wizard/review.html.twig', [
            'wizard_data' => $wizardData,
            'current_step' => 8,
            'max_step' => 8
        ]);
    }
    
    #[Route('/save', name: 'app_colis_wizard_save', methods: ['GET'])]
    public function save(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $wizardData = $this->getWizardData($session);
        
        // Vérifier que les données essentielles sont présentes
        if (empty($wizardData['colis']) || empty($wizardData['destinataire'])) {
            $this->addFlash('error', 'Des informations essentielles sont manquantes.');
            return $this->redirectToRoute('app_colis_wizard_start');
        }
        
        try {
            // 1. Créer ou récupérer l'expéditeur
            $expediteur = null;
            if (!empty($wizardData['expediteur'])) {
                $expediteur = new Expediteur();
                $expediteur->setNomEntrepriseIndividu($wizardData['expediteur']['nom_entreprise_individu']);
                $expediteur->setTelephone($wizardData['expediteur']['telephone']);
                $expediteur->setEmail($wizardData['expediteur']['email']);
                $expediteur->setPays($wizardData['expediteur']['pays']);
                $expediteur->setAdresseComplete($wizardData['expediteur']['adresse_complete']);
                $entityManager->persist($expediteur);
            }
            
            // 2. Créer le destinataire
            $destinataire = new Destinataire();
            $destinataire->setNomEntrepriseIndividu($wizardData['destinataire']['nom_entreprise_individu']);
            $destinataire->setTelephone($wizardData['destinataire']['telephone']);
            $destinataire->setEmail($wizardData['destinataire']['email']);
            $destinataire->setPays($wizardData['destinataire']['pays']);
            $destinataire->setAdresseComplete($wizardData['destinataire']['adresse_complete']);
            $entityManager->persist($destinataire);
            
            // 3. Créer le colis
            $colis = new Colis();
            $colis->setCodeTracking($wizardData['colis']['codeTracking']);
            $colis->setDimensions($wizardData['colis']['dimensions']);
            $colis->setPoids((float)$wizardData['colis']['poids']);
            $colis->setValeurDeclaree((float)$wizardData['colis']['valeur_declaree']);
            $colis->setDateCreation(new \DateTime($wizardData['colis']['date_creation']));
            $colis->setNatureMarchandise($wizardData['colis']['nature_marchandise']);
            $colis->setDescriptionMarchandise($wizardData['colis']['description_marchandise']);
            $colis->setClassificationDouaniere($wizardData['colis']['classification_douaniere']);
            
            $colis->setExpediteur($expediteur);
            $colis->setDestinataire($destinataire);
            
            $entityManager->persist($colis);
            
            // 4. Créer le statut initial
            if (!empty($wizardData['statut'])) {
                $statut = new Statut();
                $statut->setTypeStatut(StatusType::from($wizardData['statut']['type_statut']));
                $statut->setDateStatut(new \DateTime($wizardData['statut']['date_statut']));
                $statut->setLocalisation($wizardData['statut']['localisation']);
                $statut->setColis($colis);
                
                $entityManager->persist($statut);
            }
            
            // 5. Créer le transport et l'association avec le colis
            if (!empty($wizardData['transport'])) {
                $transport = new Transport();
                $transport->setTypeTransport($wizardData['transport']['type_transport']);
                $transport->setCompagnieTransport($wizardData['transport']['compagnie_transport']);
                $transport->setNumeroVolNavire($wizardData['transport']['numero_vol_navire']);
                if (!empty($wizardData['transport']['date_depart'])) {
                    $transport->setDateDepart(new \DateTime($wizardData['transport']['date_depart']));
                }
                $transport->setLieuDepart($wizardData['transport']['lieu_depart']);
                $transport->setDateArrivee(new \DateTime($wizardData['transport']['date_arrivee']));
                $transport->setLieuArrivee($wizardData['transport']['lieu_arrivee']);
                
                $entityManager->persist($transport);
                
                // Association Colis-Transport
                $colisTransport = new ColisTransport();
                $colisTransport->setColis($colis);
                $colisTransport->setTransport($transport);
                $colisTransport->setDateAssociation(new \DateTime());
                
                $entityManager->persist($colisTransport);
            }
            
            // 6. Ajouter les photos
            if (!empty($wizardData['photos'])) {
                foreach ($wizardData['photos'] as $photoData) {
                    $photo = new Photo();
                    $photo->setUrlPhoto($photoData['urlPhoto']);
                    $photo->setDateUpload(new \DateTime($photoData['dateUpload']));
                    if (isset($photoData['description'])) {
                        $photo->setDescription($photoData['description']);
                    }
                    $photo->setColis($colis);
                    
                    $entityManager->persist($photo);
                }
            }
            
            // 7. Ajouter les documents
            if (!empty($wizardData['documents'])) {
                foreach ($wizardData['documents'] as $documentData) {
                    $document = new DocumentSupport();
                    $document->setNomFichier($documentData['nomFichier']);
                    $document->setTypeDocument($documentData['typeDocument']);
                    $document->setDateUpload(new \DateTime($documentData['dateUpload']));
                    $document->setCheminStockage($documentData['cheminStockage']);
                    $document->setColis($colis);
                    
                    $entityManager->persist($document);
                }
            }
            
            // Enregistrer tout en base de données
            $entityManager->flush();
            
            // Nettoyer la session
            $session->remove(self::SESSION_KEY);
            
            $this->addFlash('success', 'Le colis a été créé avec succès !');
            
            // Rediriger vers la page de détail du colis
            return $this->redirectToRoute('app_colis_show', ['id' => $colis->getId()]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
            return $this->redirectToRoute('app_colis_wizard_review');
        }
    }
    
    #[Route('/cancel', name: 'app_colis_wizard_cancel', methods: ['GET'])]
    public function cancel(SessionInterface $session): Response
    {
        // Nettoyer la session et annuler la création
        $session->remove(self::SESSION_KEY);
        
        $this->addFlash('info', 'La création du colis a été annulée.');
        
        return $this->redirectToRoute('app_colis_index');
    }
    
    #[Route('/goto/{step}', name: 'app_colis_wizard_goto', methods: ['GET'])]
    public function gotoStep(int $step, SessionInterface $session): Response
    {
        $wizardData = $this->getWizardData($session);
        
        // Vérifier que l'utilisateur a déjà atteint cette étape
        if ($step > $wizardData['max_step']) {
            $this->addFlash('error', 'Vous ne pouvez pas accéder à cette étape directement.');
            return $this->redirectToRoute('app_colis_wizard_step' . $wizardData['current_step']);
        }
        
        return $this->redirectToRoute('app_colis_wizard_step' . $step);
    }
}