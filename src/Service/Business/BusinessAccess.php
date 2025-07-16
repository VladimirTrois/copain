<?php

namespace App\Service\Business;

use App\Entity\Business;
use App\Entity\User;
use App\Repository\BusinessRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BusinessAccess
{
    public function __construct(private BusinessRepository $repo)
    {
    }

    public function getBusinessIfOwnedByUser(int $businessId, User $user): Business
    {
        $business = $this->repo->find($businessId);
        if (!$business) {
            throw new NotFoundHttpException('Business not found.');
        }

        if (!$business->isOwnedBy($user)) {
            throw new AccessDeniedException('You do not own this business.');
        }

        return $business;
    }

    public function getBusinessIfUserBelongs(int $businessId, User $user): Business
    {
        $business = $this->repo->find($businessId);
        if (!$business) {
            throw new NotFoundHttpException('Business not found.');
        }

        if (!$business->hasUser($user)) {
            throw new AccessDeniedException('You do not belong to this business.');
        }

        return $business;
    }
}
