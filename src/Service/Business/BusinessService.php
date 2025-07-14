<?php

namespace App\Service\Business;

use App\Entity\Business;
use App\Entity\User;
use App\Service\BusinessUser\BusinessUserService;

class BusinessService
{
    public function __construct(
        private BusinessUserService $businessUserService,
        private BusinessAccessGuard $accessGuard,
        private BusinessPersister $businessPersister,
        private BusinessFinder $businessFinder,
    ) {
    }

    public function listAll(): array
    {
        return $this->businessFinder->listAll();
    }

    public function findOneBy(array $criteria): Business
    {
        return $this->businessFinder->findOneBy($criteria);
    }

    public function findBy(array $criteria): array
    {
        return $this->businessFinder->findBy($criteria);
    }

    public function find(int|string $id): Business
    {
        return $this->businessFinder->find($id);
    }

    public function listUsersOfOwnedBusiness(int $businessId, User $user): array
    {
        $business = $this->accessGuard->getBusinessIfOwnedByUser($businessId, $user);

        return $business->getBusinessUsers()->toArray();
    }

    public function createBusiness(Business $business): Business
    {
        return $this->businessPersister->createBusiness($business);
    }

    public function updateBusiness(Business $business): Business
    {
        return $this->businessPersister->updateBusiness($business);
    }

    public function delete(Business $business): void
    {
        $this->businessPersister->delete($business);
    }

    public function addUserToBusiness(int $businessId, string $email, array $roles, User $owner): void
    {
        $business = $this->accessGuard->getBusinessIfOwnedByUser($businessId, $owner);
        $this->businessUserService->addUserToBusiness($business, $email, $roles);
    }
}
