<?php

namespace App\Controller;

use App\Repository\ProjectDbRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProjectDbRepository $projectDbRepository): Response
    {
        // Get the total number of projects
        $totalProjects = $projectDbRepository->count([]);

        return $this->render('base_admin.html.twig', [
            'controller_name' => 'DashboardController',
            'totalProjects' => $totalProjects,
        ]);
    }
}