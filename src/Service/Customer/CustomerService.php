<?php

namespace App\Service\Customer;

use App\Dto\Customer\Register\CustomerCreateInput;
use App\Dto\Public\PublicOrderCreateInput;
use App\Entity\Customer;
use App\Mapper\Customer\Order\OrderInputMapper;
use App\Mapper\Customer\Register\CustomerInputMapper;
use App\Service\Order\OrderPersister;

class CustomerService
{
    public function __construct(
        private CustomerInputMapper $customerInputMapper,
        private CustomerPersister $customerPersister,
        private OrderInputMapper $orderInputMapper,
        private OrderPersister $orderPersister,
        private CustomerFinder $customerFinder,
    ) {
    }

    public function createCustomer(CustomerCreateInput $input): Customer
    {
        $customer = $this->customerInputMapper->mapToEntity($input);
        $customer = $this->customerPersister->createCustomer($customer);

        return $customer;
    }

    public function createCustomerWithOrder(PublicOrderCreateInput $input): Customer
    {
        $customer = $this->createCustomer($input->customer);
        $order = $this->orderInputMapper->mapToEntity($input->order, $customer);
        $order = $this->orderPersister->createOrder($order);

        return $customer;
    }

    public function findOneBy(array $criteria): ?Customer
    {
        return $this->customerFinder->findOneBy($criteria);
    }
}
