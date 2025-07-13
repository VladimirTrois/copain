<?php

namespace App\Mapper\Customer\Business;

use App\Dto\Customer\Business\BusinessListDto;
use App\Entity\Business;

final class BusinessDtoMapper
{
    public function toListDto(Business $business): BusinessListDto
    {
        return new BusinessListDto(
            $business->getName()
        );
    }
}
