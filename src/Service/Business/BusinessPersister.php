<?php

namespace App\Service\Business;

use App\Entity\Business;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;

class BusinessPersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityValidator $validator,
    ) {
    }

    public function createBusiness(Business $business): Business
    {
        $this->validator->validate($business);

        $this->em->persist($business);
        $this->em->flush();

        return $business;
    }

    public function updateBusiness(Business $business): Business
    {
        $this->validator->validate($business);

        $this->em->flush();

        return $business;
    }

    public function delete(Business $business): void
    {
        $this->em->remove($business);
        $this->em->flush();
    }
}
