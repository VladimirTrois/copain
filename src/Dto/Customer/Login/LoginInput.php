<?php

namespace App\Dto\Customer\Login;

use Symfony\Component\Validator\Constraints as Assert;

class LoginInput
{
    #[Assert\NotBlank(message: 'Please enter your email address.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    public string $email;
}
