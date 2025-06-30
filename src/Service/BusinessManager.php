<?php

// src/Service/BusinessManager.php

namespace App\Service;

use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BusinessManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Create business from JSON string.
     *
     * @throws UnprocessableEntityHttpException if validation fails
     */
    public function createFromJson(string $json): Business
    {
        $business = $this->serializer->deserialize($json, Business::class, 'json', [
            'groups' => ['business:write'],
        ]);

        $this->validate($business);

        $this->em->persist($business);
        $this->em->flush();

        return $business;
    }

    /**
     * Update existing business from JSON string.
     *
     * @throws UnprocessableEntityHttpException if validation fails
     */
    public function updateFromJson(Business $business, string $json): Business
    {
        $this->serializer->deserialize($json, Business::class, 'json', [
            'object_to_populate' => $business,
            'groups' => ['business:write'],
        ]);

        $this->validate($business);

        $this->em->flush();

        return $business;
    }

    public function delete(Business $business): void
    {
        $this->em->remove($business);
        $this->em->flush();
    }

    /**
     * Validate the business entity with optional validation groups.
     *
     * @throws UnprocessableEntityHttpException on validation errors
     */
    private function validate(Business $business, array $groups = ['Default']): void
    {
        $errors = $this->validator->validate($business, null, $groups);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException($this->formatValidationErrors($errors));
        }
    }

    /**
     * Format validation errors as JSON string.
     */
    private function formatValidationErrors(ConstraintViolationListInterface $errors): string
    {
        $errorDetails = [];
        foreach ($errors as $error) {
            $errorDetails[] = [
                'property' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return json_encode($errorDetails);
    }
}
