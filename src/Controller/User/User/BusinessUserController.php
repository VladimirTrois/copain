<?php

namespace App\Controller\User\User;

use App\Dto\User\Owner\User\UserBusinessInput;
use App\Entity\User;
use App\Mapper\UserMapper;
use App\Service\Business\BusinessService;
use App\Service\EntityValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/businesses/{businessId}/users', name: 'business_users_')]
#[IsGranted('ROLE_USER')]
class BusinessUserController extends AbstractController
{
    public function __construct(
        private BusinessService $businessService,
        private UserMapper $userMapper,
        private EntityValidator $validator,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listBusinessUsers(int $businessId): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $businessUsers = $this->businessService->listUsersOfOwnedBusiness($businessId, $user);
        $usersDto = array_map(
            fn ($businessUser) => $this->userMapper->toListDto($businessUser->getUser()),
            $businessUsers
        );

        return $this->json($usersDto);
    }

    #[Route('', name: 'add', methods: ['POST'])]
    public function addUserToBusiness(int $businessId, Request $request): JsonResponse
    {
        /** @var User $owner */
        $owner = $this->getUser();

        /** @var UserBusinessInput $input */
        $input = $this->serializer->deserialize($request->getContent(), UserBusinessInput::class, 'json');
        $this->validator->validate($input);

        $this->businessService->addUserToBusiness($businessId, $input, $owner);

        return $this->json([
            'message' => 'User added to business',
        ], Response::HTTP_CREATED);
    }
}
