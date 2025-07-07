<?php

// src/Exception/BusinessNotFoundException.php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BusinessNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('Business not found.');
    }
}
