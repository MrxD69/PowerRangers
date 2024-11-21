<?php
//namespace App\Controller;
//
//use App\Entity\User;
//use App\Repository\UserRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//use Symfony\Component\Routing\Annotation\Route;
//
//#[Route('/auth')]
//class AuthController extends AbstractController
//{
//    #[Route('', name: 'auth_index')]
//    public function index(): Response
//    {
//        return $this->render('auth/login_register.html.twig');
//    }
//
//    #[Route('/register', name: 'auth_register', methods: ['POST'])]
//    public function register(
//        Request $request,
//        EntityManagerInterface $entityManager,
//        UserPasswordHasherInterface $passwordHasher
//    ): Response {
//        $user = new User();
//
//        $name = $request->request->get('name');
//        $email = $request->request->get('email');
//        $password = $request->request->get('password');
//        $role = $request->request->get('role');
//
//        $user->setName($name);
//        $user->setEmail($email);
//        $user->setRoles([$role]);
//        $user->setPassword($passwordHasher->hashPassword($user, $password));
//
//        $entityManager->persist($user);
//        $entityManager->flush();
//
//        $this->addFlash('success', 'Account created successfully!');
//        return $this->redirectToRoute('auth_index');
//    }
//
//    #[Route('/login', name: 'auth_login', methods: ['POST'])]
//    public function login(
//        Request $request,
//        UserRepository $userRepository,
//        UserPasswordHasherInterface $passwordHasher
//    ): Response {
//        $email = $request->request->get('email');
//        $password = $request->request->get('password');
//        $role = $request->request->get('role');
//
//        $user = $userRepository->findOneBy(['email' => $email]);
//
//        if (!$user || !$passwordHasher->isPasswordValid($user, $password) || !in_array($role, $user->getRoles())) {
//            $this->addFlash('error', 'Invalid credentials or role.');
//            return $this->redirectToRoute('auth_index');
//        }
//
//        return $this->redirectToRoute('dashboard');
//    }
//
//    #[Route('/logout', name: 'auth_logout')]
//    public function logout(): void
//    {
//        // Symfony handles this automatically
//    }
//}

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'auth_index')]
    public function index(): Response
    {
        // Pass the "activeTab" variable to the template
        return $this->render('auth/login_register.html.twig', [
            'activeTab' => 'login', // Default to the login tab
        ]);
    }

    #[Route('/auth/register', name: 'auth_register')]
    public function register(): Response
    {
        // Pass the "activeTab" variable for the register tab
        return $this->render('auth/login_register.html.twig', [
            'activeTab' => 'register',
        ]);
    }
}

