<?php

namespace App\Mapper;

use App\Dto\User\BusinessUser\BusinessUserDto;
use App\Entity\BusinessUser;

final class BusinessUserMapper
{
    public function __construct(
        private BusinessMapper $businessMapper
    ) {
    }

    public function toDto(BusinessUser $businessUser): BusinessUserDto
    {
        return new BusinessUserDto(
            $this->businessMapper->toDto($businessUser->getBusiness()),
            $businessUser->getResponsibilities()
        );
    }
}
