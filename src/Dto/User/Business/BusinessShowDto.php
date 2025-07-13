<?php

namespace App\Dto\User\Business;

final class BusinessShowDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
