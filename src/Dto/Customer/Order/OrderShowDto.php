<?php

namespace App\Dto\Customer\Order;

use App\Dto\Customer\Business\BusinessListDto;

final class OrderShowDto
{
    public function __construct(
        public string $createdAt,
        public string $pickUpDate,
        public ?bool $isPickedUp,
        public ?bool $isValidatedByBusiness,
        public BusinessListDto $business,
    ) {
    }
}
