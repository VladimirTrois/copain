<?php

namespace App\Service\DtoMapper;

use App\Dto\Business\BusinessShowDto;
use App\Entity\Business;

final class BusinessMapper
{
    public function toDto(Business $business): BusinessShowDto
    {
        return new BusinessShowDto(
            $business->getId(),
            $business->getName()
        );
    }
}
