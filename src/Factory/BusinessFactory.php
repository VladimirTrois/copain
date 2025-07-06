<?php

namespace App\Factory;

use App\Entity\Business;
use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Business>
 */
final class BusinessFactory extends PersistentProxyObjectFactory
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
        return Business::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->unique()->company(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Business $business): void {})
        ;
    }

    public static function addBusinessToUser(User $user, array $responsibilities = ['owner']): object
    {
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne([
            'user' => $user,
            'business' => $business,
            'responsibilities' => $responsibilities,
        ]);

        return $business;
    }

    public static function addBusinessesToUser(User $user, int $count, array $responsibilities = ['owner']): array
    {
        $businesses = BusinessFactory::createMany($count);
        foreach ($businesses as $business) {
            BusinessUserFactory::createOne([
                'user' => $user,
                'business' => $business,
                'responsibilities' => $responsibilities,
            ]);
        }

        return $businesses;
    }
}
