<?php

namespace App\Dto\Customer\Order\Show;

final class BusinessDto
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
