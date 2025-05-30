<?php

namespace App\Controller;

use App\Entity\ProjectDb;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Enum\UserRole;
use App\Form\ProjectDbType;
use App\Repository\ProjectDbRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project/db')]
final class ProjectDbController extends AbstractController
{
    #[Route(name: 'app_project_db_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProjectDbRepository $projectDbRepository,
        PaginatorInterface $paginator
    ): Response {
        $searchTerm = $request->query->get('search', '');

        // QueryBuilder to filter projects based on search term
        $queryBuilder = $projectDbRepository->createQueryBuilder('p')
            ->leftJoin('p.client', 'client')
            ->addSelect('client');

        if ($searchTerm) {
            $queryBuilder->where('p.domaine LIKE :search')
                ->orWhere('p.description LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        // Pagination: fetch paginated results
        $projects = $paginator->paginate(
            $queryBuilder, // QueryBuilder or Query
            $request->query->getInt('page', 1), // Current page
            6 // Number of items per page
        );

        return $this->render('project_db/index.html.twig', [
            'project_dbs' => $projects,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * @Route("/search-project", name="project_search", methods={"GET"})
     */
    public function search(Request $request, ProjectDbRepository $repository): JsonResponse
    {
        // Get the search term from the request
        $query = $request->query->get('query');

        // Check if the query is empty
        if (empty($query)) {
            return new JsonResponse([]);
        }

        // Use the repository to search projects by domaine or description
        $projects = $repository->searchProjects($query);

        // Format the projects data to return relevant information
        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'id' => $project->getId(),
                'domaine' => $project->getDomaine(),
                'description' => $project->getDescription(),
            ];
        }

        // Return the results as JSON
        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_project_db_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projectDb = new ProjectDb();
        $form = $this->createForm(ProjectDbType::class, $projectDb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associate the project with the currently logged-in client
            $user = $this->getUser();

            if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
                $this->addFlash('error', 'Only clients can create projects.');
                return $this->redirectToRoute('app_project_db_index');
            }

            $projectDb->setClient($user);

            $entityManager->persist($projectDb);
            $entityManager->flush();

            $this->addFlash('success', 'Project created successfully!');
            return $this->redirectToRoute('app_project_db_index');
        }

        return $this->render('project_db/new.html.twig', [
            'project_db' => $projectDb,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_project_db_show', methods: ['GET'])]
    public function show(ProjectDb $projectDb): Response
    {
        return $this->render('project_db/show.html.twig', [
            'project_db' => $projectDb,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_db_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProjectDb $projectDb, EntityManagerInterface $entityManager): Response
    {
        // Restrict editing to the owner of the project
        $user = $this->getUser();
        if (!$user || $user !== $projectDb->getClient()) {
            $this->addFlash('error', 'You are not authorized to edit this project.');
            return $this->redirectToRoute('app_project_db_index');
        }

        $form = $this->createForm(ProjectDbType::class, $projectDb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Project updated successfully!');
            return $this->redirectToRoute('app_project_db_index');
        }

        return $this->render('project_db/edit.html.twig', [
            'project_db' => $projectDb,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_project_db_delete', methods: ['POST'])]
    public function delete(Request $request, ProjectDb $projectDb, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $user !== $projectDb->getClient()) {
            $this->addFlash('error', 'You are not authorized to delete this project.');
            return $this->redirectToRoute('app_project_db_index');
        }

        if ($this->isCsrfTokenValid('delete' . $projectDb->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectDb);
            $entityManager->flush();

            $this->addFlash('success', 'Project deleted successfully!');
        }

        return $this->redirectToRoute('app_project_db_index');
    }

    #[Route('/project/{id}/reclamation/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function newReclamation(Request $request, ProjectDb $projectDb, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            $this->addFlash('error', 'Only freelancers can create reclamations.');
            return $this->redirectToRoute('app_project_db_show', ['id' => $projectDb->getId()]);
        }

        $reclamation = new Reclamation();
        if ($request->isMethod('POST')) {
            $message = $request->request->get('message');
            $reclamation->setMessage($message);
            $reclamation->setProjectDb($projectDb);
            $reclamation->setIdClient($projectDb->getClient());
            $reclamation->setDate(new \DateTime());
            $reclamation->setEtat('pending');

            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Reclamation created successfully!');
            return $this->redirectToRoute('app_project_db_show', ['id' => $projectDb->getId()]);
        }

        return $this->render('reclamation/new.html.twig', [
            'project' => $projectDb,
        ]);
    }
}