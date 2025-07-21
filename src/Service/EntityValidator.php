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

    /**
     * Validate the entity with optional validation groups.
     * @param array<string> $groups
     */
    public function validate(object $entity, array $groups = ['Default']): void
    {
        $errors = $this->validator->validate($entity, null, $groups);

        if (count($errors) > 0) {
            $data = array_map(fn ($e) => [
                'property' => $e->getPropertyPath(),
                'message' => $e->getMessage(),
            ], iterator_to_array($errors));

            $json = json_encode($data, JSON_THROW_ON_ERROR);

            throw new UnprocessableEntityHttpException($json);
        }
    }
}
