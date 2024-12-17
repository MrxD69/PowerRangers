<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/freelancer/{id}/profile', name: 'get_freelancer_profile', methods: ['GET'])]
    public function getFreelancerProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            return $this->render('errors/404.html.twig', [
                'message' => 'Freelancer not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->render('freelancer/freelancer_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/freelancer/profile/edit', name: 'freelancer/edit_freelancer_profile', methods: ['GET', 'POST'])]
    public function editFreelancerProfile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_FREELANCER) {
            return $this->redirectToRoute('home_index');
        }

        if ($request->isMethod('POST')) {
            // Handle file upload for profile picture
            $uploadedFile = $request->files->get('profilePicture');
            if ($uploadedFile) {
                $destination = $this->getParameter('profile_pictures_directory');
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move($destination, $newFilename);
                    $user->setProfilePicture('/uploads/profile_pictures/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload profile picture.');
                    return $this->redirectToRoute('freelancer/edit_freelancer_profile');
                }
            }

            // Retrieve and validate other form data
            $biography = $request->request->get('biography');
            $location = $request->request->get('location');
            $skills = $request->request->all('skills');
            $phone = $request->request->get('phone');

            // Social links
            $twitter = $request->request->get('twitter');
            $facebook = $request->request->get('facebook');
            $instagram = $request->request->get('instagram');
            $linkedin = $request->request->get('linkedin');
            $github = $request->request->get('github');

            // Process and validate `skills` array
            $processedSkills = [];
            if (is_array($skills)) {
                foreach ($skills as $skill) {
                    if (!empty($skill['name']) && isset($skill['progress'])) {
                        $processedSkills[] = [
                            'name' => $skill['name'],
                            'progress' => (int)$skill['progress'],
                        ];
                    }
                }
            }

            // Update the user entity
            $user->setBiography($biography);
            $user->setLocation($location);
            $user->setSkills($processedSkills);
            $user->setPhone($phone);
            $user->setTwitter($twitter);
            $user->setFacebook($facebook);
            $user->setInstagram($instagram);
            $user->setLinkedin($linkedin);
            $user->setGithub($github);

            // Save changes
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('get_freelancer_profile', ['id' => $user->getId()]);
        }

        return $this->render('freelancer/edit_freelancer_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/client/{id}/profile', name: 'get_client_profile', methods: ['GET'])]
    public function getClientProfile(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            return $this->render('errors/404.html.twig', [
                'message' => 'Client not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->render('client/client_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/client/profile/edit', name: 'client/edit_client_profile', methods: ['GET', 'POST'])]
    public function editClientProfile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::ROLE_CLIENT) {
            return $this->redirectToRoute('home_index');
        }

        if ($request->isMethod('POST')) {
            // Handle file upload for profile picture
            $uploadedFile = $request->files->get('profilePicture');
            if ($uploadedFile) {
                $destination = $this->getParameter('profile_pictures_directory');
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move($destination, $newFilename);
                    $user->setProfilePicture('/uploads/profile_pictures/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload profile picture.');
                    return $this->redirectToRoute('client/edit_client_profile');
                }
            }

            // Retrieve and validate form data
            $phone = $request->request->get('phone');
            $location = $request->request->get('location');

            // Update the user entity
            $user->setPhone($phone);
            $user->setLocation($location);

            // Save changes
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('get_client_profile', ['id' => $user->getId()]);
        }

        return $this->render('client/edit_client_profile.html.twig', [
            'user' => $user,
        ]);
    }
}
