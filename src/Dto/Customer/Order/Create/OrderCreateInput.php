<?php

namespace App\Dto\Customer\Order\Create;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class OrderCreateInput
{
    #[Assert\NotNull]
    public int $businessId;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $pickUpDate;

    /**
     * @var OrderItemInput[]
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    #[AppAssert\UniqueArticleIds]
    public array $items = [];
}
