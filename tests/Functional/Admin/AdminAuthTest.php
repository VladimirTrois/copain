<?php

namespace App\Tests\Functional\Admin;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

final class AdminAuthTest extends BaseTestCase
{
    public function testAdminLogin(): void
    {
        $client = static::createClient();

        // First register an admin
        UserFactory::createOne(array_merge([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
            'roles' => ['ROLE_ADMIN'],
        ]));

        $payload = json_encode([
            'email' => self::EMAIL_ADMIN,
            'password' => self::PASSWORD_ADMIN,
        ]);

        $this->assertNotFalse($payload);

        // Then login
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()
            ->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertArrayHasKey('token', $data);
    }
}
