<?php

namespace App\Dto\BusinessUser;

use App\Dto\Business\BusinessShowDto;

final class BusinessUserDto
{
    public function __construct(
        public BusinessShowDto $business,
        public array $responsibilities = [],
    ) {
    }
}
