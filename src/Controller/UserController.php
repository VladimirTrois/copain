<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserManager $userManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $usersDto = $this->userManager->getAllUsersListDto();

        return $this->json($usersDto, 200);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $userDto = $this->userManager->getUserShowDto($id);
        if (!$userDto) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json($userDto, 200);
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
