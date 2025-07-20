<?php

namespace App\Service\Business;

use App\Entity\Business;
use App\Entity\User;
use App\Exception\BusinessNotFoundException;
use App\Repository\BusinessRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BusinessFinder
{
    public function __construct(
        private BusinessRepository $repo
    ) {
    }

    public function listAll(): array
    {
        return $this->repo->findAll();
    }

    public function findOwned(int $id, User $user): Business
    {
        $business = $this->find($id);
        if (! $business->isOwnedBy($user)) {
            throw new AccessDeniedException();
        }

        return $business;
    }

    public function find(int|string $id): Business
    {
        $business = $this->repo->find($id);
        if (! $business) {
            throw new NotFoundHttpException('Business not found.');
        }

        return $business;
    }

    public function findOneBy(array $criteria): Business
    {
        $business = $this->repo->findOneBy($criteria);
        if (! $business) {
            throw new BusinessNotFoundException();
        }

        return $business;
    }

    public function findBy(array $criteria): array
    {
        $businesses = $this->repo->findBy($criteria);
        if (! $businesses) {
            throw new BusinessNotFoundException();
        }

        return $businesses;
    }
}
