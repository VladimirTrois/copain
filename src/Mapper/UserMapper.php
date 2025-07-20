<?php

namespace App\Mapper;

use App\Dto\User\UserListDto;
use App\Dto\User\UserShowDto;
use App\Entity\User;

final class UserMapper
{
    public function __construct(
        private BusinessUserMapper $businessUserMapper
    ) {
    }

    public function toListDto(User $user): UserListDto
    {
        return new UserListDto($user->getId(), $user->getEmail(), $user->getRoles());
    }

    public function toShowDto(User $user): UserShowDto
    {
        $businessDtos = [];
        foreach ($user->getBusinesses() as $businessUser) {
            $businessDtos[] = $this->businessUserMapper->toDto($businessUser);
        }

        return new UserShowDto($user->getId(), $user->getEmail(), $user->getRoles(), $businessDtos);
    }
}
