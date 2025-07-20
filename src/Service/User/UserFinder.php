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

    public function findBy(array $criteria): array
    {
        $users = $this->userRepository->findBy($criteria);

        if (! $users) {
            throw new UserNotFoundException();
        }

        return $users;
    }

    public function listAll(): array
    {
        $users = $this->userRepository->findAll();

        if (! $users) {
            throw new UserNotFoundException();
        }

        return $users;
    }
}
