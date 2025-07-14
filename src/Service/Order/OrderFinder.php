<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderFinder
{
    public function __construct(private OrderRepository $repo)
    {
    }

    public function find(int|string $id): Order
    {
        $order = $this->repo->find($id);
        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }

    public function listByCustomer(int $customerId): array
    {
        return $this->repo->findBy(['customer' => $customerId]);
    }

    public function listAll(): array
    {
        return $this->repo->findAll();
    }
}
