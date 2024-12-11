<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;

#[AsController]
class UserController
{
    private UserService $userService;
    private EntityManagerInterface $entityManager;

    public function __construct(UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->userService = $userService;
        $this->entityManager = $entityManager;
    }

    #[Route('/user', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
        {
            $users = $this->userService->getAllUsers();
            return new JsonResponse($users, Response::HTTP_OK);
        }

    #[Route('/user/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if($user){
            return new JsonResponse(['message'=>'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($user, Response::HTTP_OK);
    }

    #[Route('/user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->userService->createUser($data);
            return new JsonResponse($user, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}/update', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->userService->updateUser($id, $data);
            return new JsonResponse($user, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);
        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/user/{id}/role', name: 'update_user_role', methods: ['PATCH'])]
    public function updateUserRole(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $role = $data['role'] ?? null;
            if (!in_array($role, UserRole::cases())) {
                throw new \InvalidArgumentException('Invalid role provided');
            }

            $user = $this->userService->updateUserRole($id, $role);
            return new JsonResponse($user, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

}