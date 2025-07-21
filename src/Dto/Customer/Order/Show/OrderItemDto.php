<?php

namespace App\Dto\Customer\Order\Show;

final class OrderItemDto
{
    public function __construct(
        public readonly ?int $quantity,
        public readonly ?OrderItemArticleDto $article,
    ) {
    }
}
