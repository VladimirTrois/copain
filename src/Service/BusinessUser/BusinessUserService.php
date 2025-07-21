<?php

namespace App\Service\BusinessUser;

use App\Entity\Business;
use App\Entity\BusinessUser;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class BusinessUserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private EntityValidator $validator
    ) {
    }

    /**
     * @param string[] $responsibilities
     */
    public function createBusinessUser(Business $business, User $user, array $responsibilities): void
    {
        if ($business->hasUser($user)) {
            throw new ConflictHttpException('User already belongs to this business.');
        }

        $businessUser = new BusinessUser();
        $businessUser->setBusiness($business);
        $businessUser->setUser($user);
        $businessUser->setResponsibilities($responsibilities);
        $this->validator->validate($businessUser);
        $this->em->persist($businessUser);
        $this->em->flush();
    }

    /**
     * @return BusinessUser[]
     */
    public function listUsers(Business $business): array
    {
        return $business->getBusinessUsers()
            ->toArray();
    }
}
