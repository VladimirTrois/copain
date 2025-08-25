<?php

namespace App\Service\Order;

use App\Dto\Shared\Order\OrderUpdateInput;
use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Dto\User\Business\Order\List\OrderListDto;
use App\Dto\User\Business\Order\Show\OrderShowDto;
use App\Entity\Business;
use App\Entity\Order;
use App\Mapper\Customer\Order\OrderInputMapper;
use App\Mapper\User\Business\Order\OrderDtoMapper;

class OrderBusinessService
{
    public function __construct(
        private OrderFinder $orderFinder,
        private OrderDtoMapper $orderDtoMapper,
        private OrderInputMapper $orderInputMapper,
        private OrderPersister $orderPersister,
    ) {
    }

    /**
     * @return OrderListDto[]
     */
    public function listOrdersForBusiness(Business $business, OrderCriteriaInput $criteria): array
    {
        $orders = $this->orderFinder->listByBusiness($business->getId(), $criteria);

        return array_map([$this->orderDtoMapper, 'toListDto'], $orders);
    }

    public function findOrderForBusiness(int $orderId, Business $business): Order
    {
        $order = $this->orderFinder->findOneBy([
            'id' => $orderId,
            'business' => $business,
        ]);

        return $order;
    }

    public function updateOrderForBusiness(Order $order, OrderUpdateInput $orderInput, Business $business): OrderShowDto
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
