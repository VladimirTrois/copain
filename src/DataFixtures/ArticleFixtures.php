<?php

namespace App\DataFixtures;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Find all businesses and add random numbers of articles
        $businesses = BusinessFactory::all();
        foreach ($businesses as $business) {
            ArticleFactory::createMany(rand(5, 10), ['business' => $business]);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'article'];
    }

    public function getDependencies(): array
    {
        return [
            BusinessFixtures::class,
        ];
    }
}
