<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reclamation;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Public Freelancer Profile - Accessible to Everyone
     */
    #[Route('/freelancer/{id}/public-profile', name: 'get_freelancer_public_profile', methods: ['GET'])]
    public function getFreelancerPublicProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            throw $this->createNotFoundException('Freelancer profile not found.');
        }

        // Ensure a default profile picture if none exists
        $profilePicture = $user->getProfilePicture() ?: 'public/uploads/profile_pictures/khalil.jpg';

        return $this->render('freelancer/public_profile.html.twig', [
            'user' => $user,
            'profile_picture' => $profilePicture,
        ]);
    }

    /**
     * Private Freelancer Profile - For Authenticated Freelancer Only
     */
    #[Route('/freelancer/profile', name: 'get_freelancer_private_profile', methods: ['GET'])]
    public function getFreelancerPrivateProfile(): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        return $this->render('freelancer/freelancer_profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit Freelancer Profile - Restricted to Authenticated Freelancer
     */
    #[Route('/freelancer/profile/edit', name: 'freelancer_edit_profile', methods: ['GET', 'POST'])]
    public function editFreelancerProfile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            $this->addFlash('error', 'Access denied.');
            return $this->redirectToRoute('home_index');
        }

        if ($request->isMethod('POST')) {
            $biography = $request->request->get('biography');
            $location = $request->request->get('location');
            $skills = $request->request->all('skills');
            $phone = $request->request->get('phone');
            $twitter = $request->request->get('twitter');
            $facebook = $request->request->get('facebook');
            $instagram = $request->request->get('instagram');
            $linkedin = $request->request->get('linkedin');
            $github = $request->request->get('github');

            // Handle profile picture upload
            $uploadedFile = $request->files->get('profilePicture');
            if ($uploadedFile) {
                $destination = $this->getParameter('profile_pictures_directory');
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move($destination, $newFilename);
                    $user->setProfilePicture('/uploads/profile_pictures/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload profile picture.');
                }
            }

            // Update user entity
            $user->setBiography($biography);
            $user->setLocation($location);
            $user->setSkills($skills);
            $user->setPhone($phone);
            $user->setTwitter($twitter);
            $user->setFacebook($facebook);
            $user->setInstagram($instagram);
            $user->setLinkedin($linkedin);
            $user->setGithub($github);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('get_freelancer_private_profile');
        }

        return $this->render('freelancer/edit_freelancer_profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Public Client Profile - Accessible to Everyone
     */
    #[Route('/client/{id}/profile', name: 'get_client_profile', methods: ['GET'])]
    public function getClientProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            throw $this->createNotFoundException('Client profile not found.');
        }

        // Retrieve reclamations made against the client by freelancers
        $reclamations = $this->entityManager->getRepository(Reclamation::class)
            ->createQueryBuilder('r')
            ->join('r.projectDb', 'p')
            ->where('p.client = :client')
            ->setParameter('client', $user)
            ->getQuery()
            ->getResult();

        return $this->render('client/client_profile.html.twig', [
            'user' => $user,
            'reclamations' => $reclamations,
        ]);
    }

    /**
     * Edit Client Profile - Restricted to Authenticated Client
     */
    #[Route('/client/profile/edit', name: 'client_edit_profile', methods: ['GET', 'POST'])]
    public function editClientProfile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            $this->addFlash('error', 'Access denied.');
            return $this->redirectToRoute('home_index');
        }

        if ($request->isMethod('POST')) {
            $location = $request->request->get('location');
            $phone = $request->request->get('phone');

            // Handle profile picture upload
            $uploadedFile = $request->files->get('profilePicture');
            if ($uploadedFile) {
                $destination = $this->getParameter('profile_pictures_directory');
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move($destination, $newFilename);
                    $user->setProfilePicture('/uploads/profile_pictures/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload profile picture.');
                }
            }

            $user->setLocation($location);
            $user->setPhone($phone);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('get_client_profile', ['id' => $user->getId()]);
        }

        return $this->render('client/edit_client_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/{id}/profile', name: 'get_admin_profile', methods: ['GET'])]
    public function getAdminProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_ADMIN) {
            throw $this->createNotFoundException('Admin profile not found.');
        }

        return $this->render('admin/admin_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/profile', name: 'get_admin_private_profile', methods: ['GET'])]
    public function getAdminPrivateProfile(): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_ADMIN) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        return $this->render('admin/admin_profile.html.twig', [
            'user' => $user,
        ]);
    }
}