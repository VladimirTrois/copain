<?php

namespace App\Service\Business;

use App\Dto\User\Owner\User\UserBusinessInput;
use App\Entity\Business;
use App\Entity\User;
use App\Service\BusinessUser\BusinessUserService;
use App\Service\User\UserService;

class BusinessService
{
    public function __construct(
        private BusinessUserService $businessUserService,
        private BusinessAccess $accessGuard,
        private BusinessPersister $businessPersister,
        private BusinessFinder $businessFinder,
        private UserService $userService
    ) {
    }

    /**
     * @return Business[]
     */
    public function listAll(): array
    {
        return $this->businessFinder->listAll();
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): Business
    {
        return $this->businessFinder->findOneBy($criteria);
    }

    /**
     * @param array<string, mixed> $criteria
     * @return Business[]
     */
    public function findBy(array $criteria): array
    {
        return $this->businessFinder->findBy($criteria);
    }

    public function find(int|string $id): Business
    {
        return $this->businessFinder->find($id);
    }

    /**
     * @return \App\Entity\BusinessUser[]
     */
    public function listUsersOfOwnedBusiness(int $businessId, User $user): array
    {
        $business = $this->accessGuard->getBusinessIfOwnedByUser($businessId, $user);

        return $business->getBusinessUsers()
            ->toArray();
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

    public function addUserToBusiness(int $businessId, UserBusinessInput $input, User $owner): void
    {
        $business = $this->accessGuard->getBusinessIfOwnedByUser($businessId, $owner);

        $user = $this->userService->findOrCreateUser($input->email);

        $this->businessUserService->createBusinessUser($business, $user, $input->responsibilities);
    }

    /**
     * @return list<array<mixed>>
     */
    public function getBusinessesForUser(User $user): array
    {
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

        return $businessesData;
    }
}
