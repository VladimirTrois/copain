<?php

namespace App\Service\Order;

use App\Dto\Customer\Order\Show\OrderShowDto;
use App\Entity\Customer;
use App\Mapper\Customer\Order\OrderDtoMapper;

class OrderService
{
    public function __construct(
        private OrderFinder $orderFinder,
        private OrderDtoMapper $orderDtoMapper,
    ) {
    }

    public function listOrdersByCustomer(Customer $customer): array
    {
        $orders = $this->orderFinder->listByCustomer($customer->getId());

        return array_map([$this->orderDtoMapper, 'toListDto'], $orders);
    }

    public function findOrderForCustomer(int $orderId, Customer $customer): OrderShowDto
    {
        $order = $this->orderFinder->findOneBy(['id' => $orderId, 'customer' => $customer->getId()]);

        return $this->orderDtoMapper->toShowDto($order);
    }
}
