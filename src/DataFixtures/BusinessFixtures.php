<?php

namespace App\DataFixtures;

use App\Factory\BusinessFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class BusinessFixtures extends Fixture implements FixtureGroupInterface
{
    public const NUMBEROFBUSINESSES = 30;

    public function load(ObjectManager $manager): void
    {
        BusinessFactory::createMany(self::NUMBEROFBUSINESSES);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'business'];
    }
}
