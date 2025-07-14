<?php

namespace App\Dto\Customer\Order\Show;

final class OrderItemArticleDto
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
    ) {
    }
}
