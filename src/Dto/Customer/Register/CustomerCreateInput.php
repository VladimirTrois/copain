<?php

namespace App\Dto\Customer\Register;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerCreateInput
{
    #[Assert\NotBlank]
    public string $email;

    #[Assert\NotBlank]
    public string $phone;

    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\NotBlank]
    public string $lastName;
}
