<?php

// api/tests/AbstractTest.php

namespace App\Tests;

use App\Factory\UserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class BaseTestCase extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    public const EMAIL_USER = 'user@user.com';
    public const PASSWORD_USER = 'password';
    public const EMAIL_ADMIN = 'admin@admin.com';
    public const PASSWORD_ADMIN = 'admin';
    public const URL_BASE = 'http://localhost/api';
    public const URL_LOGIN = self::URL_BASE.'/login';

    private ?string $token = null;

    protected function createClientAsUser(array $userData = []): KernelBrowser
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
            'roles' => ['ROLE_USER'],
        ], $userData));

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $client->setServerParameter('HTTP_Authorization', 'Bearer '.$token);

        return $client;
    }

    protected function createClientAsAdmin(array $userData = []): KernelBrowser
    {
        // 1. Create client (boot kernel)
        $client = static::createClient();

        // 2. Use container from that client, not self::getContainer()
        $container = $client->getContainer();

        // 3. Create user and JWT
        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'roles' => ['ROLE_ADMIN'],
        ], $userData));

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        // 4. Set auth header manually on existing client
        $client->setServerParameter('HTTP_Authorization', 'Bearer '.$token);

        return $client;
    }
}
