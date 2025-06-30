<?php

// api/tests/BusinessTest.php

namespace App\Admin\Tests;

use App\Factory\BusinessFactory;
use App\Tests\BaseTestCase;

class BusinessTest extends BaseTestCase
{
    public const NUMBERSOFUSERS = 30;

    public function testListAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        BusinessFactory::createMany(self::NUMBERSOFUSERS);

        $client->request('GET', '/api/businesses');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFUSERS, count($data));
    }

    public function testShowAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $business = BusinessFactory::createOne();

        $client->request('GET', '/api/businesses/'.$business->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($business->getName(), $data['name']);
    }

    public function testCreateAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $payload = [
            'name' => 'newbusiness',
        ];

        $client->request(
            'POST',
            '/api/businesses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($payload['name'], $data['name']);
    }

    public function testUpdateAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $business = BusinessFactory::createOne();

        $payload = [
            'name' => 'business',
        ];

        $client->request(
            'PATCH',
            '/api/businesses/'.$business->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($payload['name'], $data['name']);
    }

    public function testDeleteAsAdmin(): void
    {
        $client = $this->createClientAsAdmin();

        $business = BusinessFactory::createOne();

        $client->request('DELETE', '/api/businesses/'.$business->getId());

        $this->assertResponseStatusCodeSame(204);
    }
}
