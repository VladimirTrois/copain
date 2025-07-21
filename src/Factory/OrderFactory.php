<?php

namespace App\Factory;

use App\Entity\Order;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Order>
 */
final class OrderFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Order::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array
    {
        $pickUpDate = self::faker()->dateTimeBetween('-6 months', '+6 months');
        $isValidatedByCustomer = self::faker()->boolean(80);
        $isValidatedByBusiness = $isValidatedByCustomer && self::faker()->boolean(80);
        $isPickedUp = $isValidatedByBusiness && $pickUpDate < new \DateTime();

        return [
            'business' => BusinessFactory::new(),
            'customer' => CustomerFactory::new(),
            'pickUpDate' => $pickUpDate,
            'isValidatedByCustomer' => $isValidatedByCustomer,
            'isValidatedByBusiness' => $isValidatedByBusiness,
            'isPickedUp' => $isPickedUp,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Order $order): void {})
        ;
    }
}
