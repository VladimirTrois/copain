<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const NUMBEROFUSERS = 30;

    // Creates real fixtures
    public const USERS = [
        ['user@user.com', 'password', ['ROLE_USER']],
        ['admin@admin.com', 'admin', ['ROLE_ADMIN']],
    ];

    public function load(ObjectManager $manager): void
    {
        $users = UserFactory::createMany(count(self::USERS), static function (int $i) {
            return [
                'email' => self::USERS[$i - 1][0],
                'password' => self::USERS[$i - 1][1],
                'roles' => self::USERS[$i - 1][2],
            ];
        });

        // UserFactory::createMany(self::NUMBEROFUSERS);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'user', 'test'];
    }

    public function getDependencies(): array
    {
        return [
        ];
    }
}
