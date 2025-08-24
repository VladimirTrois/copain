<?php

namespace App\Dto\User\Business\Order\List;

use App\Dto\Shared\Order\OrderItemDto;
use App\Dto\User\Business\Customer\CustomerListDto;

final class OrderListDto
{
    /**
     * @param OrderItemDto[] $orderItems
     */
    public function __construct(
        public ?int $id,
        public ?string $createdAt,
        public ?string $pickUpDate,
        public ?bool $isPickedUp,
        public ?bool $isValidatedByBusiness,
        public ?bool $isValidatedByCustomer,
        public ?CustomerListDto $customer,
        public array $orderItems,
    ) {
    }
}
