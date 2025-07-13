<?php

namespace App\Service\Customer;

use App\Entity\Customer;
use App\Service\Order\OrderFinder;

class OrderService
{
    public function __construct(
        private OrderFinder $orderFinder,
    ) {
    }

    public function listCustomerOrders(Customer $customer): array
    {
        return $this->orderFinder->listByCustomer($customer->getId());
    }
}
