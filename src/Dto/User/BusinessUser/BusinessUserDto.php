<?php

namespace App\Dto\User\BusinessUser;

use App\Dto\User\Business\BusinessShowDto;

final class BusinessUserDto
{
    public function __construct(
        public BusinessShowDto $business,
        public array $responsibilities = [],
    ) {
    }
}
