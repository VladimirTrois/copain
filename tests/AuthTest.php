<?php

namespace App\Tests;

use App\Factory\UserFactory;

final class AuthTest extends BaseTestCase
{
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        // First register a user
        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
            'roles' => ['ROLE_USER'],
        ]));

        // Then login
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
