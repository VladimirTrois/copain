<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;

class UserPersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityValidator $validator
    ) {
    }

    public function persist(User $user): User
    {
        $this->validator->validate($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
