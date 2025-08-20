<?php

namespace App\Dto\User\Business\Order\List;

use App\Dto\Share\Order\OrderItemDto;
use App\Dto\User\Business\Customer\CustomerListDto;

final class OrderListDto
{
    public function __construct(
        public ?int $id,
        public ?string $createdAt,
        public ?string $pickUpDate,
        public ?bool $isPickedUp,
        public ?bool $isValidatedByBusiness,
        public ?bool $isValidatedByCustomer,
        public ?CustomerListDto $customer,
        /** @var OrderItemDto[] */
        public array $orderItems,
    ) {
    }
}
