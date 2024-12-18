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
            $etat = $reclamation->getEtat();
            if (!isset($etatCounts[$etat])) {
                $etatCounts[$etat] = 0;
            }
            $etatCounts[$etat]++;
        }

        // Prepare data for the reclamations chart
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

        // Get the total number of projects and count projects by domain
        $projects = $projectDbRepository->findAll();
        $projectsByDomain = [];
        foreach ($projects as $project) {
            $domain = $project->getDomaine();
            if (!isset($projectsByDomain[$domain])) {
                $projectsByDomain[$domain] = 0;
            }
            $projectsByDomain[$domain]++;
        }

        // Separate data for use in the chart
        $projectDomains = array_keys($projectsByDomain);
        $projectCounts = array_values($projectsByDomain);

        // Retrieve reponses from the database
        $reponseRepository = $entityManager->getRepository(Reponse::class);
        $reponses = $reponseRepository->findAll();

        // Render the template with the necessary variables
        return $this->render('base_admin.html.twig', [
            'controller_name' => 'DashboardController',
            'totalProjects' => count($projects),
            'projectDomains' => $projectDomains,
            'projectCounts' => $projectCounts,
            'chart' => $chart,
            'reclamations' => $reclamations,
            'reponses' => $reponses,
        ]);
    }
}
