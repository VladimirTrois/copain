<?php

namespace App\Security;

use App\Entity\Business;
use App\Entity\User;
use App\Enum\Responsibility;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BusinessVoter extends Voter
{
    // Supported actions
    private const ATTRIBUTES = ['VIEW', 'EDIT', 'MANAGE_USERS'];

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, self::ATTRIBUTES, true) && $subject instanceof Business;
    }

    /**
     * @param Business $business
     */
    protected function voteOnAttribute(string $attribute, $business, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (! $user instanceof User) {
            return false;
        }

        $businessUser = $this->getBusinessUser($business, $user);
        if (! $businessUser) {
            return false;
        }

        $roles = $businessUser->getResponsibilities();

        // OWNER can do anything
        if (in_array(Responsibility::OWNER->value, $roles, true)) {
            return true;
        }

        // MANAGER can view and edit business info, but not manage users
        if (in_array(Responsibility::MANAGER->value, $roles, true)) {
            return in_array($attribute, ['VIEW', 'EDIT'], true);
        }

        // Others no access to business-level management
        return false;
    }

    private function getBusinessUser(Business $business, User $user)
    {
        foreach ($business->getBusinessUsers() as $businessUser) {
            if ($businessUser->getUser() === $user) {
                return $businessUser;
            }
        }

        return null;
    }
}
