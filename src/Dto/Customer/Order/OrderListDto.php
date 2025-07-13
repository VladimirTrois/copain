<?php

namespace App\Dto\Customer\Order;

use App\Dto\Customer\Business\BusinessListDto;

final class OrderListDto
{
    public function __construct(
        public int $id,
        public string $createdAt,
        public string $pickUpDate,
        public ?bool $isPickedUp,
        public ?bool $isValidatedByBusiness,
        public BusinessListDto $business,
    ) {
    }
}
