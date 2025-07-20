<?php

namespace App\Mapper\Customer\Order;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Update\OrderUpdateInput;
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

        $business = $this->businessFinder->find($input->businessId);

        $order->setCustomer($customer);
        $order->setBusiness($business);
        $order->setPickUpDate(new \DateTime($input->pickUpDate));

        foreach ($input->items as $itemInput) {
            $article = $this->articleFinder->find($itemInput->articleId);

            $orderItem = new OrderItem();
            $orderItem->setArticle($article);
            $orderItem->setQuantity($itemInput->quantity);
            $orderItem->setOrder($order);

            $order->addOrderItem($orderItem);
        }

        return $order;
    }

    public function mapToExistingEntity(Order $order, OrderUpdateInput $input): Order
    {
        // Update pick-up date if set
        if (! empty($input->pickUpDate)) {
            $order->setPickUpDate(new \DateTime($input->pickUpDate));
        }

        // Update order items if set
        if (! empty($input->items)) {
            // Remove all existing order items
            foreach ($order->getOrderItems() as $existingItem) {
                $order->removeOrderItem($existingItem);
            }

            // Add all new items from input
            foreach ($input->items as $itemInput) {
                $article = $this->articleFinder->find($itemInput->articleId);

                $orderItem = new OrderItem();
                $orderItem->setArticle($article);
                $orderItem->setQuantity($itemInput->quantity);
                $orderItem->setOrder($order);

                $order->addOrderItem($orderItem);
            }
        }

        return $order;
    }
}
