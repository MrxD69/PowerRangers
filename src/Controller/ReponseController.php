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
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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

        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('reponse/indexRl.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{lid}', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $lid): Response
    {
        $reponse = new Reponse();
        $reponse->setDate(new \DateTime('today'));

        $reclamation = $this->entityManager->getRepository(Reclamation::class)->find($lid);

        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found for ID ' . $lid);
        }

        $reclamation->setEtat("traité");
        $reponse->setReclamation($reclamation);

        $form = $this->createForm(Reponse2Type::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reponse);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reclamationc', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/reponse/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse): Response
    {
        $form = $this->createForm(Reponse2Type::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reponse);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reponse/{id}/delete', name: 'app_reponse_del', methods: ['POST', 'GET'])]
    public function del(int $id, Request $request): Response
    {
        $reponse = $this->entityManager->getRepository(Reponse::class)->find($id);

        if (!$reponse) {
            throw new NotFoundHttpException("Reponse not found for ID $id");
        }

        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_token');

            if (!$this->isCsrfTokenValid('delete' . $reponse->getId(), $submittedToken)) {
                return $this->redirectToRoute('app_reponse_index');
            }
        }

        $this->entityManager->remove($reponse);
        $this->entityManager->flush();

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

    #[Route('/delete/multiple', name: 'app_reclamationc_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $selectedIds = $request->get('selectedIds', []);

        foreach ($selectedIds as $id) {
            $reclamation = $reclamationRepository->find($id);
            if ($reclamation) {
                $reclamation->setEtat('traité');
                $this->entityManager->remove($reclamation);
            }
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_reclamationc', [], Response::HTTP_SEE_OTHER);
    }
}
