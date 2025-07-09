<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Factory\CustomerFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture implements FixtureGroupInterface
{
    public const CUSTOMER_EMAIL = 'customer@customer.com';

    public function load(ObjectManager $manager): void
    {
        // Basic Customer
        $user = CustomerFactory::createOne([
            'email' => self::CUSTOMER_EMAIL,
        ]);

        CustomerFactory::createMany(9);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'customer'];
    }
}
