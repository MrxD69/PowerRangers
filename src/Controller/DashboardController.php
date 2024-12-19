<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\EvenementRepository;
use App\Repository\ProjectDbRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        EvenementRepository $evenementRepository,
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

        // Prepare data for the chart
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

        // Count Clients and Freelancers
        $userRepository = $entityManager->getRepository(User::class);
        $totalClients = $userRepository->count(['role' => UserRole::ROLE_CLIENT]);
        $totalFreelancers = $userRepository->count(['role' => UserRole::ROLE_FREELANCER]);

        // Prepare data for circular chart
        $userRolesData = [
            'labels' => ['Clients', 'Freelancers'],
            'data' => [$totalClients, $totalFreelancers]
        ];

        // Retrieve responses from the database
        $reponseRepository = $entityManager->getRepository(Reponse::class);
        $reponses = $reponseRepository->findAll();

        // Retrieve events from the database
        $evenements = $evenementRepository->findAll();

        // Prepare data for the participants chart
        $eventParticipantCounts = [];
        foreach ($evenements as $evenement) {
            $eventParticipantCounts[$evenement->getNom()] = count($evenement->getParticipants());
        }

        $labelsEvents = array_keys($eventParticipantCounts);
        $dataParticipants = array_values($eventParticipantCounts);

        $chartEvents = $chartBuilder
            ->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => $labelsEvents,
                'datasets' => [
                    [
                        'label' => 'Participants per Event',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1,
                        'data' => $dataParticipants,
                    ],
                ],
            ])
            ->setOptions([
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Number of Participants',
                        ],
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Events',
                        ],
                    ],
                ],
            ]);

        return $this->render('base_admin.html.twig', [
            'controller_name' => 'DashboardController',
            'totalProjects' => count($projects),
            'projectDomains' => array_keys($projectsByDomain),
            'projectCounts' => array_values($projectsByDomain),
            'totalClients' => $totalClients,
            'totalFreelancers' => $totalFreelancers,
            'userRolesData' => $userRolesData,
            'chart' => $chart,
            'chartEvents' => $chartEvents,
            'reclamations' => $reclamations,
            'reponses' => $reponses,
            'evenements' => $evenements,
        ]);
    }
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/dashboard/users', name: 'admin_users')]
    public function manageUsers(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Handle form submissions
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            switch ($action) {
                case 'create':
                    $user = new User();
                    $user->setEmail($request->request->get('email'));
                    $user->setNom($request->request->get('nom'));
                    $user->setPrenom($request->request->get('prenom'));
                    $user->setPassword($this->passwordHasher->hashPassword($user, $request->request->get('password')));
                    $user->setRole(UserRole::from($request->request->get('role')));

                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', 'User created successfully');
                    break;

                case 'update':
                    $user = $entityManager->getRepository(User::class)->find($request->request->get('id'));
                    if ($user) {
                        $user->setEmail($request->request->get('email'));
                        $user->setNom($request->request->get('nom'));
                        $user->setPrenom($request->request->get('prenom'));
                        $user->setRole(UserRole::from($request->request->get('role')));

                        if ($password = $request->request->get('password')) {
                            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
                        }

                        $entityManager->flush();
                        $this->addFlash('success', 'User updated successfully');
                    }
                    break;

                case 'delete':
                    $user = $entityManager->getRepository(User::class)->find($request->request->get('id'));
                    if ($user) {
                        $entityManager->remove($user);
                        $entityManager->flush();
                        $this->addFlash('success', 'User deleted successfully');
                    }
                    break;
            }

            return $this->redirectToRoute('admin_users');
        }

        // Fetch all users
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'roles' => [
                'ROLE_ADMIN' => UserRole::ROLE_ADMIN,
                'ROLE_CLIENT' => UserRole::ROLE_CLIENT,
                'ROLE_FREELANCER' => UserRole::ROLE_FREELANCER,
            ]
        ]);
    }
}
