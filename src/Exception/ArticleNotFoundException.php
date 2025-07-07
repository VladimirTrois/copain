<?php

// src/Exception/BusinessNotFoundException.php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('Article not found.');
    }
}
