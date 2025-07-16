<?php

namespace App\Mapper\Customer\Register;

use App\Dto\Customer\Register\CustomerCreateInput;
use App\Entity\Customer;

class CustomerInputMapper
{
    public function __construct(
    ) {
    }

    public function mapToEntity(CustomerCreateInput $input): Customer
    {
        $customer = new Customer();

        $customer->setEmail($input->email);
        $customer->setPhoneNumber($input->phone);
        $customer->setFirstName($input->firstName);
        $customer->setLastName($input->lastName);

        return $customer;
    }
}
