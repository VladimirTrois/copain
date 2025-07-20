<?php

namespace App\Dto\User\BusinessUser;

use App\Dto\User\Business\BusinessShowDto;
use App\Enum\Responsibility;

final class BusinessUserDto
{
    /**
     * @param Responsibility[] $responsibilities
     */
    public function __construct(
        public BusinessShowDto $business,
        public array $responsibilities = [],
    ) {
    }
}
