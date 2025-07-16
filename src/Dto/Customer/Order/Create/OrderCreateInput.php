<?php

namespace App\Dto\Customer\Order\Create;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderCreateInput
{
    #[Assert\NotBlank]
    public int $businessId;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $pickUpDate;

    /**
     * @var OrderItemInput[]
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
