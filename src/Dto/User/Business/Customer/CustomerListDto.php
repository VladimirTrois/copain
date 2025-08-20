<?php

namespace App\Dto\User\Business\Customer;

class CustomerListDto
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $phone,
    ) {
    }
}
