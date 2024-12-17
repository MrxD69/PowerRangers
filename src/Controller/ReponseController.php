<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\Reponse2Type;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use App\Service\GoogleTranslatorService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route(name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/l', name: 'app_reclamationc', methods: ['GET'])]
    public function indexp(Request $request, PaginatorInterface $paginator, ReclamationRepository $reclamationRepository): Response
    {

        $reclamations = $reclamationRepository->findAll();

        // Paginate the reclamations with 4 items per page
        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1),
            4
        );

        return $this->render('reponse/indexRl.html.twig', [
            'pagination' => $pagination,
            'hasPastDueReclamations' => !empty($alerts), // Flag indicating past due reclamations exist
        ]);
    }

    #[Route('/{lid}', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $lid): Response
    {
        $reponse = new Reponse();
        $reponse->setDate(new \DateTime('today'));

        // Fetch the Reclamation object corresponding to the given ID
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($lid);

        // Check if a Reclamation object was found
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found for ID ' . $lid);
        }

        // Set the etat attribute of the Reclamation object to true
        $reclamation->setEtat("traité");

        // Set the Reclamation property with the fetched Reclamation object
        $reponse->setReclamation($reclamation);

        $form = $this->createForm(Reponse2Type::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/reponse/{id}', name: 'app_reponse_show')]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }



    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Reponse2Type::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/reponse/{id}/delete', name: 'app_reponse_del', methods: ['POST', 'GET'])]
    public function del(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Find the Reponse entity by ID
        $reponse = $entityManager->getRepository(Reponse::class)->find($id);

        if (!$reponse) {
            throw new NotFoundHttpException("Reponse not found for ID $id");
        }

        // Validate CSRF token if the request is a POST
        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_token');

            if (!$this->isCsrfTokenValid('delete' . $reponse->getId(), $submittedToken)) {
                return $this->redirectToRoute('app_reponse_index');
            }
        }

        // Remove the entity
        $entityManager->remove($reponse);
        $entityManager->flush();

        $this->addFlash('success', 'Reponse deleted successfully.');

        return $this->redirectToRoute('app_reponse_index');
    }
    #[Route('/translate/{idCommentaire}', name: 'app_commentaire_translate', methods: ['POST'])]
    public function translate(Request $request, GoogleTranslatorService $translator, int $idCommentaire): Response
    {

        $langFrom = 'fr';
        $langTo = 'ar';
        $commentText = $request->request->get('comment_text');


        $translatedComment = $translator->translate($langFrom, $langTo, $commentText);

        return $this->json(['translated_comment' => $translatedComment]);
    }

}
