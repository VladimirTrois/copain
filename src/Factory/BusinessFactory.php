<?php

namespace App\Factory;

use App\Entity\Business;
use App\Entity\User;
use App\Enum\Responsibility;
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
     * @param Responsibility[] $responsibilities
     * @return Business
     */
    public static function addBusinessToUser(User $user, array $responsibilities = [Responsibility::OWNER]): object
    {
        $business = self::createOne();
        BusinessUserFactory::createOne([
            'user' => $user,
            'business' => $business,
            'responsibilities' => $responsibilities,
        ]);

        return $business;
    }

    /**
     * @param Responsibility[] $responsibilities
     * @return Business[]
     */
    public static function addBusinessesToUser(
        User $user,
        int $count,
        array $responsibilities = [Responsibility::OWNER]
    ): array {
        $businesses = self::createMany($count);
        foreach ($businesses as $business) {
            BusinessUserFactory::createOne([
                'user' => $user,
                'business' => $business,
                'responsibilities' => $responsibilities,
            ]);
        }

        return $businesses;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     * @return array<string, mixed>
     */
    protected function defaults(): array
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
}
