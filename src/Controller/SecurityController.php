<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
#[Route('/login', name: 'app_login')]
public function login(): Response
{
if ($this->getUser()) {
return $this->redirectToRoute('home_index');
}

return $this->render('auth/login.html.twig');
}

#[Route('/register', name: 'app_register')]
public function register(
Request $request,
UserPasswordHasherInterface $passwordHasher,
EntityManagerInterface $entityManager
): Response {
$user = new User();
$form = $this->createForm(RegistrationFormType::class, $user);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
$user->setPassword(
$passwordHasher->hashPassword(
$user,
$form->get('password')->getData() // Using the form's password field
)
);

$entityManager->persist($user);
$entityManager->flush();

$this->addFlash('success', 'Registration successful!');
return $this->redirectToRoute('app_login');
}

return $this->render('auth/register.html.twig', [
'registrationForm' => $form->createView(),
]);
}

#[Route('/logout', name: 'app_logout')]
public function logout(): void
{
throw new \LogicException('This method can be blank.');
}
}