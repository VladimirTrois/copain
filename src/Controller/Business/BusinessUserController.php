<?php

// src/Controller/BusinessUserController.php

namespace App\Controller\Business;

use App\Repository\BusinessRepository;
use App\Service\DtoMapper\UserMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BusinessUserController extends AbstractController
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private UserMapper $userMapper,
    ) {
    }

    #[Route('/api/businesses/{businessId}/users', name: 'business_users_list', methods: ['GET'])]
    public function listUsers(int $businessId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $business = $this->businessRepository->find($businessId);
        if (!$business) {
            return $this->json(['error' => 'Business not found'], 404);
        }

        // Check if the current user is an owner of this business
        $isOwner = false;
        foreach ($business->getBusinessUsers() as $businessUser) {
            if ($businessUser->getUser()->getUserIdentifier() === $user->getUserIdentifier()
                && in_array('owner', $businessUser->getResponsibilities(), true)
            ) {
                $isOwner = true;
                break;
            }
        }

        if (!$isOwner) {
            throw new AccessDeniedException('You do not have permission to view users of this business.');
        }

        // Map the users of the business to DTOs
        $businessUsers = $business->getBusinessUsers();
        $usersDto = [];
        foreach ($businessUsers as $businessUser) {
            $usersDto[] = $this->userMapper->toListDto($businessUser->getUser());
        }

        return $this->json($usersDto);
    }
}
