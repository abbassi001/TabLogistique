<?php

namespace App\Controller;

use App\Entity\Colis;
use App\Entity\Photo;
use App\Entity\Statut;
use App\Entity\DocumentSupport;
use App\Entity\ColisTransport;
use App\Form\ColisType;
use App\Form\PhotoType;
use App\Form\StatutType;
use App\Form\DocumentSupportType;
use App\Form\ColisTransportType;
use App\Repository\ColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Enum\StatusType; // Ensure the correct namespace for StatusType


#[Route('/colis')]
final class ColisController extends AbstractController
{
    #[Route(name: 'app_colis_index', methods: ['GET'])]
    public function index(ColisRepository $colisRepository): Response
    {
        return $this->render('colis/index.html.twig', [
            'colis' => $colisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_colis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $coli = new Colis();
        $form = $this->createForm(ColisType::class, $coli);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($coli);
            $entityManager->flush();

            return $this->redirectToRoute('app_colis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('colis/new.html.twig', [
            'coli' => $coli,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_colis_show', methods: ['GET'])]
    public function show(Colis $coli): Response
    {
        // Création des formulaires vides pour les actions rapides
        $photo = new Photo();
        $photo->setColis($coli);
        $photoForm = $this->createForm(PhotoType::class, $photo, [
            'action' => $this->generateUrl('app_colis_add_photo', ['id' => $coli->getId()]),
        ]);
        
        $document = new DocumentSupport();
        $document->setColis($coli);
        $documentForm = $this->createForm(DocumentSupportType::class, $document, [
            'action' => $this->generateUrl('app_colis_add_document', ['id' => $coli->getId()]),
        ]);
        
        $statut = new Statut();
        $statut->setColis($coli);
        $statutForm = $this->createForm(StatutType::class, $statut, [
            'action' => $this->generateUrl('app_colis_add_statut', ['id' => $coli->getId()]),
        ]);
        
        $transport = new ColisTransport();
        $transport->setColis($coli);
        $transportForm = $this->createForm(ColisTransportType::class, $transport, [
            'action' => $this->generateUrl('app_colis_add_transport', ['id' => $coli->getId()]),
        ]);
        
        return $this->render('colis/show.html.twig', [
            'coli' => $coli,
            'photo_form' => $photoForm->createView(),
            'document_form' => $documentForm->createView(),
            'statut_form' => $statutForm->createView(),
            'transport_form' => $transportForm->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_colis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ColisType::class, $coli);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_colis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('colis/edit.html.twig', [
            'coli' => $coli,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_colis_delete', methods: ['POST'])]
    public function delete(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coli->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($coli);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_colis_index', [], Response::HTTP_SEE_OTHER);
    }
    
    // Nouvelles méthodes pour les actions rapides
    
    #[Route('/{id}/add-photo', name: 'app_colis_add_photo', methods: ['POST'])]
    public function addPhoto(Request $request, Colis $coli, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $photo = new Photo();
        $photo->setColis($coli);
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement de l'image si nécessaire
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
                } catch (FileException $e) {
                    // Gestion de l'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo.');
                }
            }
            
            $photo->setDateUpload(new \DateTime());
            $entityManager->persist($photo);
            $entityManager->flush();
            
            $this->addFlash('success', 'La photo a été ajoutée avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }
    
    #[Route('/{id}/add-document', name: 'app_colis_add_document', methods: ['POST'])]
    public function addDocument(Request $request, Colis $coli, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $document = new DocumentSupport();
        $document->setColis($coli);
        $form = $this->createForm(DocumentSupportType::class, $document);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement du document si nécessaire
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
                } catch (FileException $e) {
                    // Gestion de l'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du document.');
                }
            }
            
            $document->setDateUpload(new \DateTime());
            $entityManager->persist($document);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le document a été ajouté avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }
    
    #[Route('/{id}/add-statut', name: 'app_colis_add_statut', methods: ['POST'])]
    public function addStatut(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        $statut = new Statut();
        $statut->setColis($coli);
        $form = $this->createForm(StatutType::class, $statut);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $statut->setDateStatut(new \DateTime());
            $entityManager->persist($statut);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le statut a été mis à jour avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }

    // Dans src/Controller/ColisController.php
#[Route('/{id}/update-statut', name: 'app_colis_update_statut', methods: ['POST'])]
public function updateStatut(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
{
    // Créer un nouveau statut
    $statut = new Statut();
    $statut->setColis($coli);
    $statut->setDateStatut(new \DateTime());
    
    // Récupérer les données du formulaire
    $statutType = $request->request->get('statut_type'); // Récupère la valeur du champ 'statut_type'
    $localisation = $request->request->get('localisation'); // Récupère la valeur du champ 'localisation'
    
    // Définir le type de statut
    $statut->setTypeStatut(StatusType::from($statutType));
    $statut->setLocalisation($localisation);
    
    // Sauvegarder dans la base de données
    $entityManager->persist($statut);
    $entityManager->flush();
    
    // Ajouter un message flash
    $this->addFlash('success', 'Le statut du colis a été mis à jour avec succès.');
    
    // Rediriger vers la page de détail du colis
    return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
}
    
    #[Route('/{id}/add-transport', name: 'app_colis_add_transport', methods: ['POST'])]
    public function addTransport(Request $request, Colis $coli, EntityManagerInterface $entityManager): Response
    {
        $transport = new ColisTransport();
        $transport->setColis($coli);
        $form = $this->createForm(ColisTransportType::class, $transport);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $transport->setDateAssociation(new \DateTime());
            $entityManager->persist($transport);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le transport a été ajouté avec succès.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_colis_show', ['id' => $coli->getId()]);
    }
}