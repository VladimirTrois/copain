<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidator
{
    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    public function validate(object $entity, array $groups = ['Default']): void
    {
        $errors = $this->validator->validate($entity, null, $groups);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException(json_encode(array_map(fn ($e) => [
                'property' => $e->getPropertyPath(),
                'message' => $e->getMessage(),
            ], iterator_to_array($errors))));
        }
    }
}
