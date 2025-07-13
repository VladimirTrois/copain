<?php

namespace App\Controller\BusinessUser;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class BusinessController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    #[Route('/users/{id}/businesses', name: 'api_user_businesses', methods: ['GET'])]
    public function listUserBusinesses(int $id): JsonResponse
    {
        $currentUser = $this->getUser();

        // Fetch the user by id
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Authorization check: allow if admin or if fetching own businesses
        if (
            !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)
            && $currentUser->getUserIdentifier() !== $user->getUserIdentifier()
        ) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        // Get businesses via businessUser relationship
        $businessUsers = $user->getBusinesses(); // collection of BusinessUser entities

        $businessesData = [];
        foreach ($businessUsers as $businessUser) {
            $business = $businessUser->getBusiness();
            $businessesData[] = [
                'id' => $business->getId(),
                'name' => $business->getName(),
                'responsibilities' => $businessUser->getResponsibilities(),
            ];
        }

        return $this->json($businessesData);
    }

    // Shortcut route for the authenticated user to list their businesses
    #[Route('/me/businesses', name: 'api_me_businesses', methods: ['GET'])]
    public function listMyBusinesses(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $businessUsers = $user->getBusinesses();

        $businessesData = [];
        foreach ($businessUsers as $businessUser) {
            $business = $businessUser->getBusiness();
            $businessesData[] = [
                'id' => $business->getId(),
                'name' => $business->getName(),
                'responsibilities' => $businessUser->getResponsibilities(),
            ];
        }

        return $this->json($businessesData);
    }
}
