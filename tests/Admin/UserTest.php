<?php

// api/tests/UserTest.php

namespace App\Admin\Tests;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

class UserTest extends BaseTestCase
{
    public const NUMBERSOFUSERS = 30;

    public function testListAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        UserFactory::createMany(self::NUMBERSOFUSERS);

        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFUSERS, count($data));
    }

    public function testShowAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $user = UserFactory::createOne();

        $client->request('GET', '/api/users/'.$user->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($user->getEmail(), $data['email']);
    }

    public function testCreateAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $payload = [
            'email' => 'newuser@example.com',
            'plainPassword' => 'newpassword',
            'roles' => ['ROLE_USER'],
        ];

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($payload['email'], $data['email']);
    }

    public function testUpdateAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $user = UserFactory::createOne(['roles' => []]);

        $payload = [
            'email' => 'updated@example.com',
            'roles' => ['ROLE_ADMIN'],
        ];

        $client->request(
            'PATCH',
            '/api/users/'.$user->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($payload['email'], $data['email']);
        $this->assertContains('ROLE_ADMIN', $data['roles']);
    }

    public function testDeleteAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $user = UserFactory::createOne();

        $client->request('DELETE', '/api/users/'.$user->getId());

        $this->assertResponseStatusCodeSame(204);
    }
}
