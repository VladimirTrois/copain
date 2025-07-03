<?php

// src/Controller/BusinessUserController.php

namespace App\Controller\Business;

use App\Repository\BusinessRepository;
use App\Repository\UserRepository;
use App\Service\BusinessManager;
use App\Service\DtoMapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BusinessUserController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private UserRepository $userRepository,
        private BusinessManager $businessManager,
        private UserMapper $userMapper,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/businesses/{businessId}/users', name: 'business_users_list', methods: ['GET'])]
    public function listUsers(int $businessId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $business = $this->businessManager->getBusinessIfOwnedByUser($businessId, $user);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 403);
        }

        $usersDto = array_map(
            fn ($businessUser) => $this->userMapper->toListDto($businessUser->getUser()),
            $business->getBusinessUsers()->toArray()
        );

        return $this->json($usersDto);
    }

    #[Route('/api/businesses/{businessId}/users', name: 'business_users_add', methods: ['POST'])]
    public function addUserToBusiness(int $businessId, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $responsibilities = $data['responsibilities'] ?? [];

        if (!$email || !is_array($responsibilities)) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        try {
            $business = $this->businessManager->getBusinessIfOwnedByUser($businessId, $currentUser);
            $this->businessManager->addUserToBusiness($business, $email, $responsibilities, $currentUser);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }

        return $this->json(['message' => 'User added to business'], 201);
    }
}
