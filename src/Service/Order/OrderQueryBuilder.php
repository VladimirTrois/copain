<?php

namespace App\Service\Order;

use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Repository\OrderRepository;
use Doctrine\ORM\Query;

class OrderQueryBuilder
{
    public function __construct(
        private OrderRepository $orderRepository,
    ) {
    }

    public function createQueryListOrderByBusiness(?int $businessId, OrderCriteriaInput $criteria): Query
    {
        $qb = $this->orderRepository->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->addSelect('c')
            ->andWhere('o.business = :business')
            ->setParameter('business', $businessId);

        // Date range filtering
        $start = null;
        $end = null;
        if ($criteria->pickUpDate) {
            $start = $criteria->pickUpDate->setTime(0, 0, 0);
            $end = $criteria->pickUpDate->setTime(23, 59, 59);
        }
        if ($criteria->pickUpFrom) {
            $start = $criteria->pickUpFrom;
        }
        if ($criteria->pickUpTo) {
            $end = $criteria->pickUpTo;
        }
        if ($start && $end) {
            $qb->andWhere('o.pickUpDate BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        } elseif ($start) {
            $qb->andWhere('o.pickUpDate >= :start')
                ->setParameter('start', $start);
        } elseif ($end) {
            $qb->andWhere('o.pickUpDate <= :end')
                ->setParameter('end', $end);
        }

        // Boolean flags filtering
        foreach (['isPickedUp', 'isValidatedByBusiness', 'isValidatedByCustomer'] as $flag) {
            if ($criteria->{$flag} !== null) {
                $qb->andWhere("o.{$flag} = :{$flag}")
                    ->setParameter($flag, $criteria->{$flag});
            }
        }

        // Customer name filtering
        if ($criteria->customerFirstName) {
            $qb->andWhere('c.firstName LIKE :customerFirstName')
                ->setParameter('customerFirstName', "%{$criteria->customerFirstName}%");
        }
        if ($criteria->customerLastName) {
            $qb->andWhere('c.lastName LIKE :customerLastName')
                ->setParameter('customerLastName', "%{$criteria->customerLastName}%");
        }

        // Ordering
        $allowedOrderFields = [
            'pickUpDate' => 'o.pickUpDate',
            'customerFirstName' => 'c.firstName',
            'customerLastName' => 'c.lastName',
        ];
        if ($criteria->orderBy && isset($allowedOrderFields[$criteria->orderBy])) {
            $direction = ($criteria->orderDir === 'desc') ? 'DESC' : 'ASC';
            $qb->orderBy($allowedOrderFields[$criteria->orderBy], $direction);
        }

        return $qb->getQuery();
    }
}
