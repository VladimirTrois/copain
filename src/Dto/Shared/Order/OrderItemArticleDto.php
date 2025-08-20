<?php

namespace App\Dto\Shared\Order;

final class OrderItemArticleDto
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?int $price,
    ) {
    }
}
