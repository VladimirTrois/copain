<?php

namespace App\Mapper\Customer\Order;

use App\Dto\Customer\Order\List\OrderListDto;
use App\Dto\Customer\Order\Show\BusinessDto;
use App\Dto\Customer\Order\Show\OrderItemArticleDto;
use App\Dto\Customer\Order\Show\OrderItemDto;
use App\Dto\Customer\Order\Show\OrderShowDto;
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
            id: $order->getId(),
            createdAt: $order->getCreatedAt()->format('Y-m-d H:i:s'),
            pickUpDate: $order->getPickUpDate()->format('Y-m-d H:i:s'),
            isPickedUp: $order->isPickedUp(),
            isValidatedByBusiness: $order->isValidatedByBusiness(),
            business: $this->businessDtoMapper->toListDto($order->getBusiness()),
        );
    }

    public function toShowDto(Order $order): OrderShowDto
    {
        $orderItems = array_map(
            fn ($item) => new OrderItemDto(
                id: $item->getId(),
                quantity: $item->getQuantity(),
                article: new OrderItemArticleDto(
                    name: $item->getArticle()->getName(),
                    price: $item->getArticle()->getPrice(),
                )
            ),
            $order->getOrderItems()->toArray()
        );

        return new OrderShowDto(
            createdAt: $order->getCreatedAt()->format(DATE_ATOM),
            pickUpDate: $order->getPickUpDate()->format(DATE_ATOM),
            isPickedUp: $order->isPickedUp(),
            isValidatedByBusiness: $order->isValidatedByBusiness(),
            business: new BusinessDto(
                name: $order->getBusiness()->getName(),
            ),
            orderItems: $orderItems,
        );
    }
}
