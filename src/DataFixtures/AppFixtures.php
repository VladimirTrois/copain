<?php

namespace App\DataFixtures;

use App\Factory\BusinessFactory;
use App\Factory\BusinessUserFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public const NUMBEROFBUSINESSES = 30;

    public function load(ObjectManager $manager): void
    {
        // $user = UserFactory::createOne();

        // $business = BusinessFactory::createOne();

        // BusinessUserFactory::createOne(['user' => $user, 'business' => $business]);

        // $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'test'];
    }
}
