<?php

namespace App\Service\Customer;

use App\Entity\Customer;
use App\Repository\CustomerRepository;

class CustomerFinder
{
    public function __construct(
        private CustomerRepository $customerRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?Customer
    {
        return $this->customerRepository->findOneBy($criteria);
    }
}
