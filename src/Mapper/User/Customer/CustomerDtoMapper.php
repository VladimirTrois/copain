<?php

namespace App\Mapper\User\Customer;

use App\Dto\User\Business\Customer\CustomerListDto;
use App\Entity\Customer;

final class CustomerDtoMapper
{
    public function toListDto(Customer $customer): CustomerListDto
    {
        return new CustomerListDto(
            firstName: $customer->getFirstName(),
            lastName: $customer->getLastName(),
            phone: $customer->getPhoneNumber(),
        );
    }
}
