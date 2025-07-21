<?php

namespace App\Dto\User\Owner\User;

use App\Enum\Responsibility;
use Symfony\Component\Validator\Constraints as Assert;

class UserBusinessInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    /**
     * @var string[]
     */
    #[Assert\NotNull]
    #[Assert\All([
        new Assert\Choice(
            callback: [Responsibility::class, 'values'],
            message: 'Invalid responsibility "{{ value }}".'
        ),
    ])]
    public array $responsibilities;
}
