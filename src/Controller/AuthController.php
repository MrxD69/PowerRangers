<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ClientRegistrationFormType;
use App\Form\FreelancerRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'auth_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ClientRegistrationFormType::class, new User());

        return $this->render('auth/login_register.html.twig', [
            'activeTab' => 'login',
            'last_username' => '',
            'error' => null,
            'registrationForm' => $form->createView(),
            
        ]);
    }

    #[Route('/auth/login', name: 'auth_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    // Redirect users based on roles after login
    if ($this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('../../templates/admin/index.html.twig');
    }
    if ($this->isGranted('ROLE_FREELANCER')) {
        return $this->redirectToRoute('../../templates/freelancer/index.html.twig');
    }
    if ($this->isGranted('ROLE_CLIENT')) {
        return $this->redirectToRoute('../../templates/client/index.html.twig');
    }

    return $this->render('auth/login_register.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
        'activeTab' => 'login',
    ]);
}

#[Route('/auth/register', name: 'auth_register', methods: ['GET', 'POST'])]
public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $user = new User();
    $form = $this->createForm(ClientRegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $role = $request->request->get('role');

        if ($role === 'freelancer') {
            $user->setRole('ROLE_FREELANCER');
        } elseif ($role === 'client') {
            $user->setRole('ROLE_CLIENT');
        } elseif ($role === 'admin') {
            $user->setRole('ROLE_ADMIN');
        } else {
            $this->addFlash('error', 'Invalid role selected.');
            return $this->redirectToRoute('auth_register');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $user->getEmail())) {
            $this->addFlash('error', 'Invalid email format.');
            return $this->redirectToRoute('auth_register');
        }


        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Invalid email format.');
            return $this->redirectToRoute('auth_register');
        }
        
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Registration successful! You can now log in.');
        return $this->redirectToRoute('auth_login');
    }

    return $this->render('auth/login_register.html.twig', [
        'registrationForm' => $form->createView(),
        'activeTab' => 'register',
    ]);
}


    #[Route('/auth/logout', name: 'auth_logout')]
    public function logout(): void
    {
        // Symfony handles this automatically
    }
}
