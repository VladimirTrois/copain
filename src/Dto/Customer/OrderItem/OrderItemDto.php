<?php

namespace App\Dto\Customer\OrderItem;

final class OrderItemDto
{
    public function __construct(
        public int $id,
        public int $quantity,
    ) {
    }
}
