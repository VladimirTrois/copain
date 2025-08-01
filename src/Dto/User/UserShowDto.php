<?php

namespace App\Dto\User;

use App\Dto\BusinessUser\BusinessUserDto;

final class UserShowDto
{
    /**
     * @param BusinessUserDto[] $businesses
     */
    public function __construct(
        public int $id,
        public string $email,
        public array $roles,
        public array $businesses,
    ) {
    }
}
