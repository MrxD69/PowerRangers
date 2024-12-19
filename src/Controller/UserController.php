<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\UserRole;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Freelancer Public Profile
    #[Route('/freelancer/{id}/public-profile', name: 'get_freelancer_public_profile', methods: ['GET'])]
    public function getFreelancerPublicProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            throw $this->createNotFoundException('Freelancer profile not found.');
        }

        $reclamationsFiled = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['complainant' => $user]);

        $reclamationsAgainst = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['againstUser' => $user]);

        return $this->render('freelancer/public_profile.html.twig', [
            'user' => $user,
            'reclamationsFiled' => $reclamationsFiled,
            'reclamationsAgainst' => $reclamationsAgainst,
        ]);
    }

    // Freelancer Private Profile
    #[Route('/freelancer/profile', name: 'get_freelancer_private_profile', methods: ['GET'])]
    public function getFreelancerPrivateProfile(): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $reclamationsFiled = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['complainant' => $user]);

        $reclamationsAgainst = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['againstUser' => $user]);

        return $this->render('freelancer/freelancer_profile.html.twig', [
            'user' => $user,
            'reclamationsFiled' => $reclamationsFiled,
            'reclamationsAgainst' => $reclamationsAgainst,
        ]);
    }

    // Edit Freelancer Profile
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
            $skills = $request->request->all('skills'); // Fetch array of skills
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

            // Save the updated user data
            $user->setBiography($biography);
            $user->setLocation($location);
            $user->setSkills(array_values($skills)); // Ensure it's a numeric array
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


    // Client Profile
    #[Route('/client/{id}/profile', name: 'get_client_profile', methods: ['GET'])]
    public function getClientProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            throw $this->createNotFoundException('Client profile not found.');
        }

        $reclamationsFiled = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['complainant' => $user]);

        $reclamationsAgainst = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['againstUser' => $user]);

        return $this->render('client/client_profile.html.twig', [
            'user' => $user,
            'reclamationsFiled' => $reclamationsFiled,
            'reclamationsAgainst' => $reclamationsAgainst,
        ]);
    }

    // Client Private Profile
    #[Route('/client/profile', name: 'get_client_private_profile', methods: ['GET'])]
    public function getClientPrivateProfile(): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        // Fetch reclamations filed by the client
        $reclamationsFiled = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['complainant' => $user]);

        // Fetch reclamations against the client
        $reclamationsAgainst = $this->entityManager->getRepository(Reclamation::class)
            ->findBy(['againstUser' => $user]);

        return $this->render('client/client_profile.html.twig', [
            'user' => $user,
            'reclamationsFiled' => $reclamationsFiled,
            'reclamationsAgainst' => $reclamationsAgainst,
        ]);
    }
    #[Route('/client/profile/edit', name: 'client_edit_profile', methods: ['GET', 'POST'])]
    public function editClientProfile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $location = $request->request->get('location');

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

            // Save the updated user data
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setLocation($location);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('get_client_private_profile');
        }

        return $this->render('client/edit_client_profile.html.twig', [
            'user' => $user,
        ]);
    }


    // Admin Profile
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

    // Admin Private Profile
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
