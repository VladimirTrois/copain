<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;

class UserFinder
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function find(int $id): User
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $criteria
     * @return User[]
     */
    public function findBy(array $criteria): array
    {
        $users = $this->userRepository->findBy($criteria);

        if (! $users) {
            throw new UserNotFoundException();
        }

        return $users;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?User
    {
        $user = $this->userRepository->findOneBy($criteria);

        return $user;
    }

    /**
     * @return User[]
     */
    public function listAll(): array
    {
        $users = $this->userRepository->findAll();

        if (! $users) {
            throw new UserNotFoundException();
        }

        return $users;
    }
}
