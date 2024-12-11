<?php

namespace App\Services;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }
    public function createUser(array $data): ?User
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setRole(UserRole::from($data['role']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();;

        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->userRepository->find($id);
        if(!$user){
            throw new \Exception('User not Found');
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }

        $this->entityManager->flush();


        return $user;
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        if($user){
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }

    public function updateUserRole(int $id, string $role): User
    {
        $user = $this->userRepository->find($id);
        if(!$user){
            throw new \Exception('User not found');
        }

        $user->setRole(UserRole::from($role));
        $this->entityManager->flush();

        return $user;

    }


}