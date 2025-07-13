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
        $business = $this->repo->find($id);
        if (!$business) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $business;
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
