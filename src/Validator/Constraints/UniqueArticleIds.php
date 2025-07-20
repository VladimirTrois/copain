<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UniqueArticleIds extends Constraint
{
    public string $message = 'Duplicate articleId {{ articleId }} found in order items.';

    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
