<?php

// src/Controller/UserController.php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Service\UserInvitationManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        private UserInvitationManager $userInvitationManager,
    ) {
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $usersDto = $this->userManager->getAllUsersListDto();

        return $this->json($usersDto, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $userDto = $this->userManager->getUserShowDto($id);
        if (!$userDto) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($userDto, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // 1. Create the user (but no password yet)
            $user = $this->userManager->createFromJson($request->getContent());

            // 2. Send invitation email with password setup token
            $this->userInvitationManager->sendInvitation($user);

            // 3. Return success
            return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $updatedUser = $this->userManager->updateFromJson($user, $request->getContent());

            return $this->json($updatedUser, 200, [], ['groups' => ['user:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->userManager->delete($user);

        // 204 No Content is appropriate for successful deletes without body
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
