<?php

namespace App\Dto\Customer\Order\Show;

final class OrderShowDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $createdAt,
        public readonly string $pickUpDate,
        public readonly ?bool $isPickedUp,
        public readonly ?bool $isValidatedByBusiness,
        public readonly BusinessDto $business,
        /** @var OrderItemDto[] */
        public readonly array $orderItems,
    ) {
    }
}
