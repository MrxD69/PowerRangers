<?php

namespace App\Controller;

use App\Entity\CommandeDb;
use App\Form\CommandeDbType;
use App\Repository\CommandeDbRepository;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande/db')]
final class CommandeDbController extends AbstractController {
    #[Route(name: 'app_commande_db_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CommandeDbRepository $commandeDbRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search', '');

        $queryBuilder = $commandeDbRepository->createQueryBuilder('p');

        if ($searchTerm) {
            $queryBuilder->where('p.technologie LIKE :search')
                ->orWhere('p.description LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1), /*page number*/
            6/*limit per page*/
        );

        return $this->render('commande_db/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/new', name: 'app_commande_db_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || !$this->isGranted('ROLE_FREELANCER')) {
            throw $this->createAccessDeniedException('Only freelancers can create offers.');
        }

        $commandeDb = new CommandeDb();
        $commandeDb->setFreelancer($user); // Set the freelancer

        $form = $this->createForm(CommandeDbType::class, $commandeDb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commandeDb);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_db_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande_db/new.html.twig', [
            'commande_db' => $commandeDb,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_commande_db_show', methods: ['GET'])]
    public function show(CommandeDb $commandeDb): Response
    {
        return $this->render('commande_db/show.html.twig', [
            'commande_db' => $commandeDb,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_db_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CommandeDb $commandeDb, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeDbType::class, $commandeDb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_db_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande_db/edit.html.twig', [
            'commande_db' => $commandeDb,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_db_delete', methods: ['POST'])]
    public function delete(Request $request, CommandeDb $commandeDb, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commandeDb->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commandeDb);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_db_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_commande_db_pdf', methods: ['GET'])]
    public function generatePdf(CommandeDb $commandeDb, PdfGenerator $pdfGenerator): Response
    {
        // Create the HTML content
        $html = $this->renderView('commande_db/pdf_template.html.twig', [
            'commande_db' => $commandeDb,
        ]);

        // Generate the PDF
        $pdfContent = $pdfGenerator->generatePdf($html);

        // Output PDF to browser
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="commande_' . $commandeDb->getId() . '.pdf"',
            ]
        );
    }
}