<?php

// src/Service/UserManager.php

namespace App\Service\User;

use App\Dto\User\UserListDto;
use App\Dto\User\UserShowDto;
use App\Entity\User;
use App\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $hasher,
        private UserMapper $userMapper,
        private UserFinder $userFinder,
    ) {
    }

    /**
     * Get all users as a list of ListDto.
     *
     * @return UserListDto[]
     */
    public function getAllUsersListDto(): array
    {
        $users = $this->userFinder->listAll();

        return array_map(fn ($user) => $this->userMapper->toListDto($user), $users);
    }

    public function findUser(int $id): User
    {
        return $this->userFinder->find($id);
    }

    public function mapUserToShowDto(User $user): UserShowDto
    {
        return $this->userMapper->toShowDto($user);
    }

    /**
     * Create user from JSON string.
     */
    public function createFromJson(string $json): User
    {
        $user = $this->serializer->deserialize($json, User::class, 'json', [
            'groups' => ['user:write'],
        ]);

        $this->validate($user, ['Default', 'create']);
        $this->hashPasswordIfNeeded($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Update existing user from JSON string.
     */
    public function updateFromJson(User $user, string $json): User
    {
        $this->serializer->deserialize($json, User::class, 'json', [
            'object_to_populate' => $user,
            'groups' => ['user:write'],
        ]);

        $this->validate($user, ['Default', 'update']);
        $this->hashPasswordIfNeeded($user);

        $this->em->flush();

        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Validate the user entity with optional validation groups.
     * @param array<int, mixed> $groups
     */
    private function validate(User $user, array $groups = ['Default']): void
    {
        $errors = $this->validator->validate($user, null, $groups);

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

    private function hashPasswordIfNeeded(User $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (! $plainPassword) {
            return;
        }

        $hashed = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        // Clear plain password after hashing for security
        $user->eraseCredentials();
    }
}
