<?php

namespace App\Mapper\User\Owner\User;

use App\Dto\User\Owner\User\UserBusinessInput;
use App\Entity\BusinessUser;
use App\Entity\User;

class UserInputMapper
{
    public function __construct(
    ) {
    }

    public function mapToEntity(UserBusinessInput $input): BusinessUser
    {
        $user = new User();
        $user->setEmail($input->email);

        $businessUser = new BusinessUser();

        $businessUser->setResponsibilities($input->responsibilities);
        $businessUser->setUser($user);

        return $businessUser;
    }
}
