<?php

namespace App\Mapper\Customer\Order;

use App\Dto\Customer\Order\OrderListDto;
use App\Entity\Order;
use App\Mapper\Customer\Business\BusinessDtoMapper;
use App\Mapper\Customer\OrderItems\OrderItemDtoMapper;

final class OrderDtoMapper
{
    public function __construct(
        private BusinessDtoMapper $businessDtoMapper,
        private OrderItemDtoMapper $orderItemDtoMapper,
    ) {
    }

    public function toListDto(Order $order): OrderListDto
    {
        return new OrderListDto(
            $order->getId(),
            $order->getCreatedAt()->format('Y-m-d H:i:s'),
            $order->getPickUpDate()->format('Y-m-d H:i:s'),
            $order->isPickedUp(),
            $order->isValidatedByBusiness(),
            $this->businessDtoMapper->toListDto($order->getBusiness()),
        );
    }
}
