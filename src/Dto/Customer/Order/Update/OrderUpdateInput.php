<?php

namespace App\Dto\Customer\Order\Update;

use App\Dto\Customer\Order\Create\OrderItemInput;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class OrderUpdateInput
{
    #[Assert\Date]
    #[Assert\NotBlank(allowNull: true)]
    public ?string $pickUpDate = null;

    /**
     * @var OrderItemInput[]|null
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'You must provide at least one item.')]
    #[AppAssert\UniqueArticleIds]
    public ?array $items = null;
}
