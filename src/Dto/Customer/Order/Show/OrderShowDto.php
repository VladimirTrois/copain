<?php

namespace App\Dto\Customer\Order\Show;

final class OrderShowDto
{
    public function __construct(
        public readonly string $createdAt,
        public readonly string $pickUpDate,
        public readonly ?bool $isPickedUp,
        public readonly ?bool $isValidatedByBusiness,
        /** @var OrderItemDto[] */
        public readonly BusinessDto $business,
        public readonly array $orderItems,
    ) {
    }
}
