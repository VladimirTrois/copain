<?php

namespace App\Dto\Shared\Order;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderItemInput
{
    #[Assert\NotNull]
    public int $articleId;

    #[Assert\Positive]
    public int $quantity;
}
