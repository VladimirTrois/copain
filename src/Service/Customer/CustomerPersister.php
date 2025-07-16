<?php

namespace App\Service\Customer;

use App\Entity\Customer;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;

class CustomerPersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityValidator $validator,
    ) {
    }

    public function createCustomer(Customer $customer): Customer
    {
        $this->validator->validate($customer);

        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }
}
