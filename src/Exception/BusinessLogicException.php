<?php

// src/Exception/BusinessNotFoundException.php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BusinessLogicException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(400, $message);
    }
}
