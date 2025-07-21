<?php

// api/tests/AbstractTest.php

namespace App\Tests;

use App\Factory\CustomerFactory;
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

    public const EMAIL_CUSTOMER = 'customer@customer.com';

    private ?string $token = null;

    /**
     * @param string[] $userData
     */
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
        $this->token = $jwtManager->create($user);

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $this->token);

        return $client;
    }

    /**
     * @param string[] $userData
     */
    protected function createClientAsAdmin(array $userData = []): KernelBrowser
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'roles' => ['ROLE_ADMIN'],
        ], $userData));

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $this->token = $jwtManager->create($user);

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $this->token);

        return $client;
    }

    /**
     * @param string[] $userData
     */
    protected function createClientAsCustomer(array $userData = []): KernelBrowser
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $customer = CustomerFactory::createOne(array_merge([
            'email' => self::EMAIL_CUSTOMER,
        ], $userData));

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $this->token = $jwtManager->create($customer);

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $this->token);

        return $client;
    }

    protected function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Decode JSON response content.
     *
     * @return array<string, mixed>
     */
    protected function decodeResponse(KernelBrowser $client): array
    {
        $content = $client->getResponse()
            ->getContent();

        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);

        $this->assertIsArray($data, 'Response content should be an array');

        return $data;
    }

    /**
     * @param array<mixed> $payload
     */
    protected function encodeJson(array $payload): string
    {
        $json = json_encode($payload);
        $this->assertNotFalse($json, 'JSON encoding failed');

        return $json;
    }
}
