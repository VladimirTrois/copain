<?php

namespace App\Controller\BusinessUser;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Business\BusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api')]
class BusinessController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private BusinessService $businessService,
    ) {
    }

    #[Route('/users/{id}/businesses', name: 'api_user_businesses', methods: ['GET'])]
    public function listUserBusinesses(int $id, UserInterface $currentUser): JsonResponse
    {
        // Fetch the user by id
        $user = $this->userRepository->find($id);
        if (! $user) {
            return $this->json([
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Authorization check: allow if admin or if fetching own businesses
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        $isSelf = $currentUser->getUserIdentifier() === $user->getUserIdentifier();
        if (! $isAdmin && ! $isSelf) {
            return $this->json([
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $businesses = $this->businessService->getBusinessesForUser($user);

        return $this->json($businesses);
    }

    // Shortcut route for the authenticated user to list their businesses
    #[Route('/me/businesses', name: 'api_me_businesses', methods: ['GET'])]
    public function listMyBusinesses(UserInterface $user): JsonResponse
    {
        $businesses = $this->businessService->getBusinessesForUser($user);

        return $this->json($businesses);
    }
}
