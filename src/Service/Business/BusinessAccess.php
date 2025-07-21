<?php

namespace App\Service\Business;

use App\Entity\Business;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class BusinessAccess
{
    public function __construct(
        private BusinessFinder $businessFinder,
    ) {
    }

    public function getBusinessIfOwnedByUser(int $businessId, User $user): Business
    {
        $business = $this->businessFinder->find($businessId);

        if (! $business->isOwnedBy($user)) {
            throw new AccessDeniedException('You do not own this business.');
        }

        return $business;
    }

    public function getBusinessIfUserBelongs(int $businessId, UserInterface $user): Business
    {
        $business = $this->businessFinder->find($businessId);

        if (! $business->hasUser($user)) {
            throw new AccessDeniedException('You do not belong to this business.');
        }

        return $business;
    }
}
