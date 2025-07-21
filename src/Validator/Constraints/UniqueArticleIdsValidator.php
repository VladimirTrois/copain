<?php

namespace App\Validator\Constraints;

use App\Dto\Customer\Order\Create\OrderItemInput;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueArticleIdsValidator extends ConstraintValidator
{
    /**
     * @param OrderItemInput[] $value
     * @param UniqueArticleIds $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (! is_array($value)) {
            return;
        }

        $seen = [];

        foreach ($value as $item) {
            if (! $item instanceof OrderItemInput) {
                continue;
            }

            $articleId = $item->articleId;
            if (in_array($articleId, $seen, true)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ articleId }}', (string) $articleId)
                    ->addViolation();

                return;
            }

            $seen[] = $articleId;
        }
    }
}
