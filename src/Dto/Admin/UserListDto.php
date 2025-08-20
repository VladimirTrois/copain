<?php

namespace App\Dto\Admin;

final class UserListDto
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public ?int $id,
        public string $email,
        public array $roles = [],
    ) {
    }
}
