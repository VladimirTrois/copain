<?php

// src/Controller/UserController.php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Service\User\UserInvitationService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private UserInvitationService $userInvitationService,
    ) {
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $usersDto = $this->userService->getAllUsersListDto();

        return $this->json($usersDto, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $userDto = $this->userService->getUserShowDto($id);
        if (!$userDto) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($userDto, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // 1. Create the user (but no password yet)
        $user = $this->userService->createFromJson($request->getContent());

        // 2. Send invitation email with password setup token
        $this->userInvitationService->sendPasswordSetUpInvitation($user);

        // 3. Return success
        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $updatedUser = $this->userService->updateFromJson($user, $request->getContent());

        return $this->json($updatedUser, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->userService->delete($user);

        // 204 No Content is appropriate for successful deletes without body
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
