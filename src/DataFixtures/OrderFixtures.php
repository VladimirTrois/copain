<?php

namespace App\DataFixtures;

use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        // Retrieve all Customers and Businesses
        $customers = CustomerFactory::all();
        $businesses = BusinessFactory::all();

        // For each customer, select a random number of businesses.
        foreach ($customers as $customer) {
            $businessCount = rand(1, count($businesses));
            for ($i = 0; $i < $businessCount; ++$i) {
                $business = $businesses[$i];
                // Then for each business, create an order for that business.
                $order = OrderFactory::createOne(['customer' => $customer, 'business' => $business]);
                // Then add to that order a random number of OrderItems with a random article from that business
                $articleCount = $business->getArticles()->count();
                $numberOfItems = rand(1, $articleCount);
                for ($j = 0; $j < $numberOfItems; ++$j) {
                    OrderItemFactory::createOne(['order' => $order, 'article' => $business->getArticles()->get($j)]);
                }
            }
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'order'];
    }
}
