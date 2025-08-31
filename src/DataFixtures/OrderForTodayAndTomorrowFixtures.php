<?php

namespace App\DataFixtures;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class OrderForTodayAndTomorrowFixtures extends Fixture implements FixtureGroupInterface
{
    public const NUMBER_OF_ORDERS = 20;

    public function load(ObjectManager $manager): void
    {
        // Retrieve all Businesses
        $businesses = BusinessFactory::all();

        // For each business add a number of orders for today.
        foreach ($businesses as $business) {
            $orders = OrderFactory::createMany(self::NUMBER_OF_ORDERS, [
                'business' => $business,
                'pickUpDate' => new \DateTime(),
            ]);
            $manager->flush();

            // For each order add a random number of items from the business
            foreach ($orders as $order) {
                $articles = ArticleFactory::findOrCreate([
                    'business' => $business,
                ]);

                foreach ($articles as $article) {
                    OrderItemFactory::createOne([
                        'order' => $order,
                        'article' => $article,
                    ]);
                }
                $manager->flush();
            }
        }
    }

    public static function getGroups(): array
    {
        return ['orderForToday'];
    }
}
