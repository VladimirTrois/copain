<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;

class OrderPersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityValidator $validator,
    ) {
    }

    public function createOrder(Order $order): Order
    {
        $this->validator->validate($order);

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    public function updateOrder(Order $order): Order
    {
        $this->validator->validate($order);

        // No need to call persist() if $order is already managed
        $this->em->flush();

        return $order;
    }
}
