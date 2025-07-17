<?php

namespace App\Service\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Show\OrderShowDto;
use App\Dto\Customer\Order\Update\OrderUpdateInput;
use App\Entity\Customer;
use App\Entity\Order;
use App\Mapper\Customer\Order\OrderDtoMapper;
use App\Mapper\Customer\Order\OrderInputMapper;

class OrderService
{
    public function __construct(
        private OrderFinder $orderFinder,
        private OrderDtoMapper $orderDtoMapper,
        private OrderInputMapper $orderInputMapper,
        private OrderPersister $orderPersister,
    ) {
    }

    public function listOrdersByCustomer(Customer $customer): array
    {
        $orders = $this->orderFinder->listByCustomer($customer->getId());

        return array_map([$this->orderDtoMapper, 'toListDto'], $orders);
    }

    public function findOrderForCustomer(int $orderId, Customer $customer): Order
    {
        $order = $this->orderFinder->findOneBy(['id' => $orderId, 'customer' => $customer->getId()]);

        return $order;
    }

    public function createOrderForCustomer(OrderCreateInput $orderInput, Customer $customer): Order
    {
        $order = $this->orderInputMapper->mapToEntity($orderInput, $customer);
        $order = $this->orderPersister->createOrder($order);

        return $order;
    }

    public function updateOrderForCustomer(Order $order, OrderUpdateInput $orderInput, Customer $customer): OrderShowDto
    {
        $order = $this->orderInputMapper->mapToExistingEntity($order, $orderInput);
        $order = $this->orderPersister->updateOrder($order);

        return $this->orderDtoMapper->toShowDto($order);
    }

    public function mapOrderToShowDto(Order $order): OrderShowDto
    {
        return $this->orderDtoMapper->toShowDto($order);
    }
}
