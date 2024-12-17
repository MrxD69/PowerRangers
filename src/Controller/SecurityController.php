<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_project_db_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home_index');
        }

        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $role = $request->request->get('role');

            // Validate inputs
            if (!$nom || !$prenom || !$email || !$password || !$role) {
                $this->addFlash('register_error', 'All fields are required.');
                return $this->redirectToRoute('app_register');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('register_error', 'Invalid email address.');
                return $this->redirectToRoute('app_register');
            }

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('register_error', 'Email is already registered.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));

            if (in_array($role, ['ROLE_CLIENT', 'ROLE_ADMIN', 'ROLE_FREELANCER'])) {
                $user->setRole(UserRole::from($role));
            } else {
                $this->addFlash('register_error', 'Invalid role selected.');
                return $this->redirectToRoute('app_register');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('register_success', 'Registration successful! You can now log in.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Generate a token and store it in the session
                $token = bin2hex(random_bytes(32));
                $session->set('password_reset_token', $token);
                $session->set('password_reset_email', $email);

                // Mock email by showing the token directly or saving it to a file
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                file_put_contents('mock_email.txt', "Password Reset Link: $resetUrl");

                $this->addFlash('success', 'A password reset link has been sent to your email address (check the mock email file).');
            } else {
                $this->addFlash('error', 'No user found with this email.');
            }

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('auth/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
    {
        // Check if the token matches the one in the session
        $storedToken = $session->get('password_reset_token');
        $email = $session->get('password_reset_email');

        if (!$storedToken || $token !== $storedToken) {
            $this->addFlash('error', 'Invalid or expired password reset token.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Find the user based on the email in the session
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', 'No user found for the provided token.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Clear the token from the session
            $session->remove('password_reset_token');
            $session->remove('password_reset_email');

            $this->addFlash('success', 'Your password has been reset successfully.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/reset_password.html.twig', [
            'token' => $token,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank.');
    }
}
