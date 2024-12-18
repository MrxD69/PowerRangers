<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Repository\ProjectDbRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProjectDbRepository $projectDbRepository,
        EntityManagerInterface $entityManager,
        ChartBuilderInterface $chartBuilder
    ): Response {
        // Retrieve reclamations from the database
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);
        $reclamations = $reclamationRepository->findAll();

        // Count the number of reclamations by state (etat)
        $etatCounts = [];
        foreach ($reclamations as $reclamation) {
            $etat = $reclamation->getEtat(); // Accessing the 'etat' of the reclamation
            if (!isset($etatCounts[$etat])) {
                $etatCounts[$etat] = 0;
            }
            $etatCounts[$etat]++;
        }

        // Prepare data for the chart
        $labels = array_keys($etatCounts);
        $data = array_values($etatCounts);

        $chart = $chartBuilder
            ->createChart(Chart::TYPE_BAR) // Bar chart for 'etat' and counts
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Reclamations by Etat',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)', // Color of the bars
                        'borderColor' => 'rgba(54, 162, 235, 1)', // Border color of bars
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

        // Get the total number of projects
        $totalProjects = $projectDbRepository->count([]);

        // Retrieve reponses from the database
        $reponseRepository = $entityManager->getRepository(Reponse::class);
        $reponses = $reponseRepository->findAll();

        // Render the template with the necessary variables
        return $this->render('base_admin.html.twig', [
            'controller_name' => 'DashboardController',
            'totalProjects' => $totalProjects,
            'chart' => $chart,
            'reclamations' => $reclamations, // Pass reclamations to the template
            'reponses' => $reponses,         // Pass reponses to the template
        ]);
    }
}
