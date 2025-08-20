<?php

namespace App\Dto\Shared\Order;

final class OrderItemDto
{
    public function __construct(
        public readonly ?int $quantity,
        public readonly ?OrderItemArticleDto $article,
    ) {
    }
}
