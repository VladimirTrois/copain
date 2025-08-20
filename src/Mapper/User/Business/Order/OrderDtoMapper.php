<?php

namespace App\Mapper\User\Business\Order;

use App\Dto\Shared\Order\OrderItemArticleDto;
use App\Dto\Shared\Order\OrderItemDto;
use App\Dto\User\Business\Order\List\OrderListDto;
use App\Entity\Order;
use App\Mapper\User\Customer\CustomerDtoMapper;

final class OrderDtoMapper
{
    public function __construct(
        private CustomerDtoMapper $customerDtoMapper,
    ) {
    }

    public function toListDto(Order $order): OrderListDto
    {
        $orderItems = array_map(
            fn ($item) => new OrderItemDto(
                quantity: $item->getQuantity(),
                article: new OrderItemArticleDto(
                    name: $item->getArticle()
                        ->getName(),
                    price: $item->getArticle()
                        ->getPrice(),
                )
            ),
            $order->getOrderItems()
                ->toArray()
        );

        return new OrderListDto(
            id: $order->getId(),
            createdAt: $order->getCreatedAt() === null ? null : $order->getCreatedAt()
                ->format('Y-m-d H:i:s'),
            pickUpDate: $order->getPickUpDate()
                ->format('Y-m-d H:i:s'),
            isPickedUp: $order->isPickedUp(),
            isValidatedByBusiness: $order->isValidatedByBusiness(),
            isValidatedByCustomer: $order->isValidatedByCustomer(),
            customer: $this->customerDtoMapper->toListDto($order->getCustomer()),
            orderItems: $orderItems,
        );
    }

    // public function toShowDto(Order $order): OrderShowDto
    // {
    //     $orderItems = array_map(
    //         fn ($item) => new OrderItemDto(
    //             quantity: $item->getQuantity(),
    //             article: new OrderItemArticleDto(
    //                 name: $item->getArticle()
    //                     ->getName(),
    //                 price: $item->getArticle()
    //                     ->getPrice(),
    //             )
    //         ),
    //         $order->getOrderItems()
    //             ->toArray()
    //     );

    //     return new OrderShowDto(
    //         id: $order->getId(),
    //         createdAt: $order->getCreatedAt() === null ? null : $order->getCreatedAt()
    //             ->format(DATE_ATOM),
    //         pickUpDate: $order->getPickUpDate()
    //             ->format(DATE_ATOM),
    //         isPickedUp: $order->isPickedUp(),
    //         isValidatedByBusiness: $order->isValidatedByBusiness(),
    //         business: new BusinessDto(name: $order->getBusiness() ->getName()),
    //         orderItems: $orderItems,
    //     );
    // }
}
