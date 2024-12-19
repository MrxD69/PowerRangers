<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\CommandeDb;
use App\Form\Reclamation2Type;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function filterBadWords(string $text): string
    {
        $badWords = ["bad1", "bad2", "bad3", "bad4", "bad5"];
        foreach ($badWords as $word) {
            $pattern = "/\b" . preg_quote($word, '/') . "\b/i";
            $text = preg_replace($pattern, "****", $text);
        }
        return $text;
    }

    #[Route('/new/commande/{commandeId}', name: 'app_reclamation_new_commande', methods: ['GET', 'POST'])]
    public function newFromClient(Request $request, int $commandeId): Response
    {
        $user = $this->getUser();

        if (!$user || !$this->isGranted('ROLE_CLIENT')) {
            throw $this->createAccessDeniedException('Only clients can file reclamations against offers.');
        }

        $commande = $this->entityManager->getRepository(CommandeDb::class)->find($commandeId);
        if (!$commande) {
            throw $this->createNotFoundException('Offer not found.');
        }

        $freelancer = $commande->getFreelancer();
        if (!$freelancer) {
            throw $this->createNotFoundException('Freelancer not associated with this offer.');
        }

        $reclamation = new Reclamation();
        $reclamation->setEtat('non traité');
        $reclamation->setDate(new \DateTime());
        $reclamation->setComplainant($user); // Client files the complaint
        $reclamation->setAgainstUser($freelancer); // The freelancer is the target
        $reclamation->setCommandeDb($commande);

        $form = $this->createForm(Reclamation2Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filteredMessage = $this->filterBadWords($reclamation->getMessage());
            $reclamation->setMessage($filteredMessage);
            $this->entityManager->persist($reclamation);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_commande_db_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
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
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $form = $this->createForm(Reclamation2Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reclamation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/multiple', name: 'app_reclamation_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request): Response
    {
        $selectedIds = $request->request->all('selectedIds');

        if (!is_array($selectedIds)) {
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        $selectedIds = array_filter($selectedIds, 'is_scalar');

        foreach ($selectedIds as $id) {
            $reclamation = $this->entityManager->getRepository(Reclamation::class)->find($id);
            if ($reclamation) {
                $this->entityManager->remove($reclamation);
            }
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reclamation/chart', name: 'app_reclamation_chart')]
    public function reclamationChart(EntityManagerInterface $entityManager, ChartBuilderInterface $chartBuilder): Response
    {
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);

        $reclamations = $reclamationRepository->findAll();

        $etatCounts = [];

        foreach ($reclamations as $reclamation) {
            $etat = $reclamation->getEtat();
            if (!isset($etatCounts[$etat])) {
                $etatCounts[$etat] = 0;
            }
            $etatCounts[$etat]++;
        }

        $labels = array_keys($etatCounts);
        $data = array_values($etatCounts);

        $chart = $chartBuilder
            ->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Reclamations by Etat',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                        'data' => $data,
                    ],
                ],
            ])
            ->setOptions([
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Number of Reclamations',
                        ],
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Etat',
                        ],
                    ],
                ],
            ]);

        return $this->render('reclamation/chart.html.twig', [
            'chart' => $chart,
        ]);
    }
}
