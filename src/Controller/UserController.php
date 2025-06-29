<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserManager $userManager,
        private UserRepository $userRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        return $this->json($users, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json($user, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->userManager->createFromJson($request->getContent());

            return $this->json($user, 201, [], ['groups' => ['user:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        try {
            $updatedUser = $this->userManager->updateFromJson($user, $request->getContent());

            return $this->json($updatedUser, 200, [], ['groups' => ['user:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $this->userManager->delete($user);

        // 204 No Content is appropriate for successful deletes without body
        return $this->json(null, 204);
    }
}
