<?php

namespace App\Service\Order;

use App\Dto\Shared\PaginatedResultDto;
use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Entity\Order;
use App\Exception\OrderNotFoundException;
use App\Repository\OrderRepository;
use App\Service\PaginationService;

class OrderFinder
{
    public function __construct(
        private OrderRepository $repo,
        private OrderQueryBuilder $orderQueryBuilder,
        private PaginationService $paginationService,
    ) {
    }

    public function find(int|string $id): Order
    {
        /** @var Order|null $order */
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

        if (! $order) {
            throw new OrderNotFoundException();
        }

        return $order;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): Order
    {
        $order = $this->repo->findOneBy($criteria);

        if (! $order) {
            throw new OrderNotFoundException();
        }

        return $order;
    }

    /**
     * @return Order[]
     */
    public function listByCustomer(?int $customerId): array
    {
        $customers = $this->repo->findBy([
            'customer' => $customerId,
        ]);

        return $customers;
    }

    public function listByBusiness(?int $businessId, OrderCriteriaInput $criteria): PaginatedResultDto
    {
        $listByBusinessQuery = $this->orderQueryBuilder->createQueryListOrderByBusiness($businessId, $criteria);

        return $this->paginationService->paginate($listByBusinessQuery, $criteria->page, $criteria->limit);
    }

    /**
     * @return Order[]
     */
    public function listAll(): array
    {
        return $this->repo->findAll();
    }
}
