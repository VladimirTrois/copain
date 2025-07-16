<?php

namespace App\Controller\BusinessUser\User;

use App\Mapper\UserMapper;
use App\Service\Business\BusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/businesses/{businessId}/users', name: 'business_users_')]
#[IsGranted('ROLE_USER')]
class BusinessUserController extends AbstractController
{
    public function __construct(
        private BusinessService $businessService,
        private UserMapper $userMapper,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listBusinessUsers(int $businessId, UserInterface $user): JsonResponse
    {
        $businessUsers = $this->businessService->listUsersOfOwnedBusiness($businessId, $user);
        $usersDto = array_map(
            fn ($businessUser) => $this->userMapper->toListDto($businessUser->getUser()),
            $businessUsers
        );

        return $this->json($usersDto);
    }

    #[Route('', name: 'add', methods: ['POST'])]
    public function addUserToBusiness(int $businessId, Request $request, UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $responsibilities = $data['responsibilities'] ?? [];

        if (!$email || !is_array($responsibilities)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $this->businessService->addUserToBusiness($businessId, $email, $responsibilities, $user);

        return $this->json(['message' => 'User added to business'], Response::HTTP_CREATED);
    }
}
