<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Service\Business\BusinessService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class BusinessController extends AbstractController
{
    public function __construct(
        private BusinessService $businessService,
        private UserService $userService,
    ) {
    }

    #[Route('/users/{id}/businesses', name: 'api_user_businesses', methods: ['GET'])]
    public function listUserBusinesses(int $id): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Fetch the user by id
        $targetUser = $this->userService->findUser($id);

        // Authorization check: allow if admin or if fetching own businesses
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);
        $isSelf = $user->getUserIdentifier() === $targetUser->getUserIdentifier();
        if (! $isAdmin && ! $isSelf) {
            return $this->json([
                'error' => 'Access denied',
            ], Response::HTTP_FORBIDDEN);
        }

        $businesses = $this->businessService->getBusinessesForUser($targetUser);

        return $this->json($businesses);
    }

    // Shortcut route for the authenticated user to list their businesses
    #[Route('/me/businesses', name: 'api_me_businesses', methods: ['GET'])]
    public function listMyBusinesses(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $businesses = $this->businessService->getBusinessesForUser($user);

        return $this->json($businesses);
    }
}
