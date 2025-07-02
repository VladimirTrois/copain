<?php

namespace App\Tests;

use App\Factory\UserFactory;

final class AdminAuthTest extends BaseTestCase
{
    public function testAdminLogin(): void
    {
        $client = static::createClient();

        // First register an admin
        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'roles' => ['ROLE_ADMIN'],
        ]));

        // Then login
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }
}
