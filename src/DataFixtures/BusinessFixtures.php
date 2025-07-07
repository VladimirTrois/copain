<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Responsibility;
use App\Factory\BusinessFactory;
use App\Factory\BusinessUserFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BusinessFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const NUMBEROFBUSINESSES = 30;

    public function load(ObjectManager $manager): void
    {
        // Add one Business owned by admin
        $admin = UserFactory::find(['email' => UserFixtures::ADMIN_EMAIL]);
        $adminBusiness = BusinessFactory::createOne(['name' => 'Admin Corp']);
        BusinessUserFactory::createOne([
            'user' => $admin,
            'business' => $adminBusiness,
            'responsibilities' => [Responsibility::OWNER],
        ]);

        // Add businesses owned by regular user
        $user = UserFactory::find(['email' => UserFixtures::USER_EMAIL]);

        $userBusinesses = [];
        for ($i = 1; $i <= 5; ++$i) {
            $business = BusinessFactory::createOne(['name' => 'User Business '.$i]);
            BusinessUserFactory::createOne([
                'user' => $user,
                'business' => $business,
                'responsibilities' => [Responsibility::OWNER],
            ]);
            $userBusinesses[] = $business;
        }

        // Assign employees to each of the regular user's businesses
        $employees = UserFactory::randomRange(5, 10); // 5 to 10 random users

        foreach ($userBusinesses as $business) {
            foreach ($employees as $employee) {
                if ($employee->getId() !== $user->getId()) {
                    BusinessUserFactory::createOne([
                        'user' => $employee,
                        'business' => $business,
                        'responsibilities' => ['employee'],
                    ]);
                }
            }
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'business'];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
