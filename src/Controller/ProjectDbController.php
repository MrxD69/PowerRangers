<?php

namespace App\Controller;

use App\Entity\ProjectDb;
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
        $queryBuilder = $projectDbRepository->createQueryBuilder('p');
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
            $entityManager->persist($projectDb);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_db_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_db/new.html.twig', [
            'project_db' => $projectDb,
            'form' => $form,
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
        $form = $this->createForm(ProjectDbType::class, $projectDb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_project_db_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_db/edit.html.twig', [
            'project_db' => $projectDb,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_db_delete', methods: ['POST'])]
    public function delete(Request $request, ProjectDb $projectDb, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projectDb->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectDb);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_db_index', [], Response::HTTP_SEE_OTHER);
    }
}