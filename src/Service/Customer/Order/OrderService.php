<?php

namespace App\Service\Customer\Order;

use App\Entity\Customer;
use App\Mapper\Customer\Order\OrderDtoMapper;
use App\Service\Order\OrderFinder;

class OrderService
{
    public function __construct(
        private OrderFinder $orderFinder,
        private OrderDtoMapper $orderDtoMapper,
    ) {
    }

    public function listCustomerOrders(Customer $customer): array
    {
        $orders = $this->orderFinder->listByCustomer($customer->getId());

        return array_map([$this->orderDtoMapper, 'toListDto'], $orders);
    }
}
