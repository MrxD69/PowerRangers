<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\Reclamation2Type;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController{
    #[Route(name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setEtat("non traité");
        $reclamation->setDate(new \DateTime('today'));

        $form = $this->createForm(Reclamation2Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Reclamation2Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
    #[Route('/l/{id}', name: 'app_reclamation_deletee', methods: ['POST'])]
    public function deletee(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamationc', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete-selected', name: 'app_reclamation_delete_selected', methods: ['POST'])]
    public function deleteSelected(Request $request, ReclamationRepository $reclamationRepository, EntityManagerInterface $entityManager): Response {
        $selectedIds = $request->request->get('selectedIds', []);

        if (empty($selectedIds)) {
            $this->addFlash('error', 'No items selected for deletion.');
            return $this->redirectToRoute('app_reclamation_index');
        }

        // Fetch entities by IDs
        $reclamations = $reclamationRepository->findBy(['id' => $selectedIds]);

        if (count($reclamations) < count($selectedIds)) {
            $missingIds = array_diff($selectedIds, array_map(fn($r) => $r->getId(), $reclamations));
            $this->addFlash('error', 'Some items could not be found: ' . implode(', ', $missingIds));
        }

        foreach ($reclamations as $reclamation) {
            $entityManager->remove($reclamation);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Selected items deleted successfully.');
        return $this->redirectToRoute('app_reclamation_index');
    }









}