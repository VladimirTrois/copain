<?php

namespace App\DataFixtures;

use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
                // Create an order for that business.
                $order = OrderFactory::createOne(['customer' => $customer, 'business' => $business]);
                // Add to that order a random number of articles from that business
                $articles = $business->getArticles()->toArray();
                if (empty($articles)) {
                    continue;
                }
                $selectedArticles = array_rand($articles, rand(2, count($articles)));
                foreach ($selectedArticles as $articleId) {
                    $article = $articles[$articleId];
                    OrderItemFactory::createOne(['order' => $order, 'article' => $article]);
                }
            }
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['all', 'order'];
    }

    public function getDependencies(): array
    {
        return [
            BusinessFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
