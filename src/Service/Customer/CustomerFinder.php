<?php

namespace App\Service\Customer;

use App\Entity\Customer;
use App\Repository\CustomerRepository;

class CustomerFinder
{
    public function __construct(
        private CustomerRepository $customerRepository, )
    {
    }

    public function findOneBy(array $criteria): ?Customer
    {
        return $this->customerRepository->findOneBy($criteria);
    }
}
