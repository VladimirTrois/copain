<?php

namespace App\Service\Order;

use App\Entity\Order;
use App\Exception\OrderNotFoundException;
use App\Repository\OrderRepository;

class OrderFinder
{
    public function __construct(private OrderRepository $repo)
    {
    }

    public function find(int|string $id): Order
    {
        $order = $this->repo->createQueryBuilder('o')
            ->leftJoin('o.orderItems', 'oi')
            ->addSelect('oi')
            ->leftJoin('oi.article', 'a')
            ->addSelect('a')
            ->leftJoin('o.business', 'b')
            ->addSelect('b')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$order) {
            throw new OrderNotFoundException();
        }

        return $order;
    }

    public function findOneBy(array $criteria): Order
    {
        $order = $this->repo->findOneBy($criteria);

        if (!$order) {
            throw new OrderNotFoundException();
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
