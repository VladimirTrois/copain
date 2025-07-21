<?php

namespace App\Dto\User;

use App\Dto\User\BusinessUser\BusinessUserDto;

final class UserShowDto
{
    /**
     * @param string[] $roles
     * @param BusinessUserDto[] $businesses
     */
    public function __construct(
        public ?int $id,
        public string $email,
        public array $roles,
        public array $businesses,
    ) {
    }
}
