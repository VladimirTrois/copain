<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const USER_EMAIL = 'user@user.com';
    public const USER_PASSWORD = 'password';
    public const ADMIN_EMAIL = 'admin@admin.com';
    public const ADMIN_PASSWORD = 'admin';

    public const NUMBEROFUSERS = 10;

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = UserFactory::createOne([
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD,
            'roles' => ['ROLE_ADMIN'],
        ]);

        // Regular User
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
            'roles' => ['ROLE_USER'],
        ]);

        UserFactory::createMany(self::NUMBEROFUSERS);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'user'];
    }
}
