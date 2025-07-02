<?php

namespace App\Tests\Adimn;

use App\Factory\BusinessFactory;
use App\Factory\BusinessUserFactory;
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
        foreach ($data as $user) {
            // Assert required keys exist
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('roles', $user);

            // Assert id is int
            $this->assertIsInt($user['id']);

            // Assert email is string and valid email format (optional)
            $this->assertIsString($user['email']);
            $this->assertMatchesRegularExpression('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $user['email']);

            // Assert roles is an array
            $this->assertIsArray($user['roles']);
        }
    }

    public function testShowAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $user = UserFactory::createOne();
        $business = BusinessFactory::createOne();
        $businessUser = BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);

        $client->request('GET', '/api/users/'.$user->getId());

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        // Top-level keys
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('businesses', $data);

        // Validate types and values for user fields
        $this->assertIsInt($data['id']);
        $this->assertSame($user->getId(), $data['id']);
        $this->assertSame($user->getEmail(), $data['email']);
        $this->assertIsArray($data['roles']);
        $this->assertContains('ROLE_USER', $data['roles']); // or assertEquals if you want exact roles

        // Validate businesses is an array with one item
        $this->assertIsArray($data['businesses']);
        $this->assertCount(1, $data['businesses']);

        $businessData = $data['businesses'][0];

        // Check businesses array item keys
        $this->assertArrayHasKey('business', $businessData);
        $this->assertArrayHasKey('responsibilities', $businessData);

        // Check business keys and types
        $this->assertIsArray($businessData['business']);
        $this->assertArrayHasKey('id', $businessData['business']);
        $this->assertArrayHasKey('name', $businessData['business']);
        $this->assertSame($business->getId(), $businessData['business']['id']);
        $this->assertSame($business->getName(), $businessData['business']['name']);

        // Check responsibilities array
        $this->assertIsArray($businessData['responsibilities']);
        $this->assertContains('owner', $businessData['responsibilities']);
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
