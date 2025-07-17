<?php

namespace App\Dto\Customer\Order\Create;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderItemInput
{
    #[Assert\NotNull]
    public int $articleId;

    #[Assert\Positive]
    public int $quantity;
}
