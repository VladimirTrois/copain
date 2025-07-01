<?php

namespace App\Dto\User;

final class UserListDto
{
    public function __construct(
        public int $id,
        public string $email,
        public array $roles = [],
    ) {
    }
}
