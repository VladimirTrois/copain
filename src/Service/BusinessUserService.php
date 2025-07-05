<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\BusinessUser;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BusinessUserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
    ) {
    }

    public function addUserToBusiness(Business $business, string $email, array $responsibilities): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($business->hasUser($user)) {
            throw new ConflictHttpException('User already belongs to this business.');
        }

        $businessUser = new BusinessUser();
        $businessUser->setBusiness($business);
        $businessUser->setUser($user);
        $businessUser->setResponsibilities($responsibilities);

        $this->em->persist($businessUser);
        $this->em->flush();
    }

    public function listUsers(Business $business): array
    {
        return $business->getBusinessUsers()->toArray();
    }
}
