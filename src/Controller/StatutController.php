<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Entity\User;
use App\Form\StatutType;
use App\Repository\StatutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/statut')]
class StatutController extends AbstractController
{
    #[Route('/', name: 'app_statut_index', methods: ['GET'])]
    public function index(StatutRepository $statutRepository): Response
    {
        return $this->render('statut/index.html.twig', [
            'statuts' => $statutRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_statut_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $statut = new Statut();
        
        // Get the current user's employee if available
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User && method_exists($user, 'getEmploye') && $user->getEmploye()) {
            $statut->setEmploye($user->getEmploye());
        }
        
        $form = $this->createForm(StatutType::class, $statut, [
            'employe_disabled' => $user instanceof \App\Entity\User && $user->getEmploye() !== null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($statut);
            $entityManager->flush();

            $this->addFlash('success', 'Le statut a été créé avec succès.');
            return $this->redirectToRoute('app_statut_index');
        }

        return $this->render('statut/new.html.twig', [
            'statut' => $statut,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_statut_show', methods: ['GET'])]
    public function show(Statut $statut): Response
    {
        return $this->render('statut/show.html.twig', [
            'statut' => $statut,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_statut_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Statut $statut, EntityManagerInterface $entityManager): Response
    {
        // Check if the current user is allowed to edit
        $user = $this->getUser();
        $canEditEmploye = true;
        
        if ($user instanceof \App\Entity\User && method_exists($user, 'getEmploye')) {
            // If user has an employee linked and it's already set on the status
            if ($user->getEmploye() && $statut->getEmploye() && $statut->getEmploye()->getId() === $user->getEmploye()->getId()) {
                $canEditEmploye = false; 
            }
        }
        
        $form = $this->createForm(StatutType::class, $statut, [
            'employe_disabled' => !$canEditEmploye
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le statut a été modifié avec succès.');
            return $this->redirectToRoute('app_statut_index');
        }

        return $this->render('statut/edit.html.twig', [
            'statut' => $statut,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_statut_delete', methods: ['POST'])]
    public function delete(Request $request, Statut $statut, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$statut->getId(), $request->request->get('_token'))) {
            $entityManager->remove($statut);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le statut a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_statut_index');
    }
}