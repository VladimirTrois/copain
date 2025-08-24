<?php

namespace App\Dto\Customer\Order\Show;

use App\Dto\Shared\Order\OrderItemDto;

final class OrderShowDto
{
    /**
     * @param OrderItemDto[] $orderItems
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $createdAt,
        public readonly ?string $pickUpDate,
        public readonly ?bool $isPickedUp,
        public readonly ?bool $isValidatedByBusiness,
        public readonly ?BusinessDto $business,
        public readonly array $orderItems,
    ) {
    }
}
