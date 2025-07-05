<?php

namespace App\Tests\User;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AuthTest extends BaseTestCase
{
    public function testUserCanLoginWithCorrectCredentials(): void
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

    public function testLoginFailsWithWrongCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserCanRefreshJwtUsingValidRefreshToken()
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

        $oldData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('refresh_token', $oldData);

        // Need to sleep to advance test environment
        // otherwise $oldData['token'] === $newData['token']
        sleep(1);

        $client->request('POST', '/api/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'refresh_token' => $oldData['refresh_token'],
        ]));

        $this->assertResponseIsSuccessful();
        $newData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $newData);
        $this->assertNotEquals($oldData['token'], $newData['token']);
    }

    public function testRefreshFailsWithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'refresh_token' => 'invalid-token',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
