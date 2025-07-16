<?php

namespace App\Mapper\Customer\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Article\ArticleFinder;
use App\Service\Business\BusinessFinder;

class OrderInputMapper
{
    public function __construct(
        private ArticleFinder $articleFinder,
        private BusinessFinder $businessFinder,
    ) {
    }

    public function mapToEntity(OrderCreateInput $input, Customer $customer): Order
    {
        $order = new Order();

        $business = $this->businessFinder->findOneBy(['id' => $input->businessId]);

        $order->setCustomer($customer);
        $order->setBusiness($business);
        $order->setPickUpDate(new \DateTime($input->pickUpDate));

        foreach ($input->items as $itemInput) {
            $article = $this->articleFinder->findOneBy(['id' => $itemInput->articleId]);
            $orderItem = new OrderItem();
            $orderItem->setArticle($article);
            $orderItem->setQuantity($itemInput->quantity);
            $orderItem->setOrder($order);
            $order->addOrderItem($orderItem);
        }

        return $order;
    }
}
