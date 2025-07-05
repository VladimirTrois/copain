<?php

// src/Controller/BusinessUserController.php

namespace App\Controller\Business;

use App\Repository\BusinessRepository;
use App\Repository\UserRepository;
use App\Service\BusinessService;
use App\Service\DtoMapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BusinessUserController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private UserRepository $userRepository,
        private BusinessService $businessService,
        private UserMapper $userMapper,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/businesses/{businessId}/users', name: 'business_users_list', methods: ['GET'])]
    public function listBusinessUsers(int $businessId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $businessUsers = $this->businessService->listUsersOfOwnedBusiness($businessId, $user);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: Response::HTTP_FORBIDDEN);
        }

        $usersDto = array_map(
            fn ($businessUser) => $this->userMapper->toListDto($businessUser->getUser()),
            $businessUsers
        );

        return $this->json($usersDto);
    }

    #[Route('/api/businesses/{businessId}/users', name: 'business_users_add', methods: ['POST'])]
    public function addUserToBusiness(int $businessId, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $responsibilities = $data['responsibilities'] ?? [];

        if (!$email || !is_array($responsibilities)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->businessService->addUserToBusiness($businessId, $email, $responsibilities, $currentUser);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['message' => 'User added to business'], Response::HTTP_CREATED);
    }
}
