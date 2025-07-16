<?php

namespace App\Dto\Public;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Register\CustomerCreateInput;
use Symfony\Component\Validator\Constraints as Assert;

final class PublicOrderCreateInput
{
    #[Assert\Valid]
    #[Assert\NotNull]
    public CustomerCreateInput $customer;

    #[Assert\Valid]
    #[Assert\NotNull]
    public OrderCreateInput $order;
}
