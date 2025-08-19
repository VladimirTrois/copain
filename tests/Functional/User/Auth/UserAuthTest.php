<?php

namespace App\Tests\Functional\User;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

final class UserAuthTest extends BaseTestCase
{
    public function testLoginSucceedsWithValidCredentials(): void
    {
        $client = static::createClient();

        // First register a user
        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
            'roles' => ['ROLE_USER'],
        ]));

        $payload = [
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
        ];

        // Then login
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($payload));

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginFailsWithWrongCredentials(): void
    {
        $client = static::createClient();

        $payload = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
        ];

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($payload));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRefreshSucceedsWithValidToken(): void
    {
        $client = static::createClient();

        // First register a user
        $user = UserFactory::createOne(array_merge([
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
            'roles' => ['ROLE_USER'],
        ]));

        $payload = [
            'email' => self::EMAIL_USER,
            'password' => self::PASSWORD_USER,
        ];

        // Then login
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($payload));

        $oldData = $this->decodeResponse($client);
        $this->assertArrayHasKey('refresh_token', $oldData);

        // Need to sleep to advance test environment
        // otherwise $oldData['token'] === $newData['token']
        sleep(1);

        $newPayload = [
            'refresh_token' => $oldData['refresh_token'],
        ];

        $client->request('POST', '/api/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($newPayload));

        $this->assertResponseIsSuccessful();
        $newData = $this->decodeResponse($client);

        $this->assertArrayHasKey('token', $newData);
        $this->assertNotEquals($oldData['token'], $newData['token']);
    }

    public function testRefreshFailsWithInvalidToken(): void
    {
        $client = static::createClient();

        $payload = [
            'refresh_token' => 'invalid-token',
        ];

        $client->request('POST', '/api/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($payload));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
