<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\BusinessUser;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BusinessManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserRepository $userRepository,
    ) {
    }

    public function createFromJson(string $json): Business
    {
        $business = $this->serializer->deserialize($json, Business::class, 'json', [
            'groups' => ['business:write'],
        ]);

        $this->validateEntity($business);

        $this->em->persist($business);
        $this->em->flush();

        return $business;
    }

    public function updateFromJson(Business $business, string $json): Business
    {
        $this->serializer->deserialize($json, Business::class, 'json', [
            'object_to_populate' => $business,
            'groups' => ['business:write'],
        ]);

        $this->validateEntity($business);

        $this->em->flush();

        return $business;
    }

    public function delete(Business $business): void
    {
        $this->em->remove($business);
        $this->em->flush();
    }

    public function getBusinessIfOwnedByUser(int $businessId, User $user): Business
    {
        $business = $this->em->getRepository(Business::class)->find($businessId);
        if (!$business) {
            throw new NotFoundHttpException('Business not found.');
        }

        if (!$business->isOwnedBy($user)) {
            throw new AccessDeniedException('You do not own this business.');
        }

        return $business;
    }

    public function addUserToBusiness(Business $business, string $email, array $responsibilities): void
    {
        $userToAdd = $this->getUserByEmail($email);

        if ($this->businessHasUser($business, $userToAdd)) {
            throw new ConflictHttpException('User already belongs to this business.');
        }

        $businessUser = new BusinessUser();
        $businessUser->setBusiness($business);
        $businessUser->setUser($userToAdd);
        $businessUser->setResponsibilities($responsibilities);

        $this->em->persist($businessUser);
        $this->em->flush();
    }

    // ----------------------------
    // Private Helpers
    // ----------------------------

    private function getUserByEmail(string $email): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }

    private function businessHasUser(Business $business, User $user): bool
    {
        foreach ($business->getBusinessUsers() as $existing) {
            if ($existing->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    private function validateEntity(object $entity, array $groups = ['Default']): void
    {
        $errors = $this->validator->validate($entity, null, $groups);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException($this->formatValidationErrors($errors));
        }
    }

    private function formatValidationErrors(ConstraintViolationListInterface $errors): string
    {
        return json_encode(array_map(fn ($error) => [
            'property' => $error->getPropertyPath(),
            'message' => $error->getMessage(),
        ], iterator_to_array($errors)));
    }
}
