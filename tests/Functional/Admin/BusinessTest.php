<?php

// api/tests/BusinessTest.php

namespace App\Tests\Functional\Admin;

use App\Factory\BusinessFactory;
use App\Tests\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class BusinessTest extends BaseTestCase
{
    private const NUMBERS_OF_USERS = 30;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        // Create client first to avoid kernel boot issues later
        $this->client = $this->createClientAsAdmin();
    }

    public function testListAsAdmin(): void
    {
        BusinessFactory::createMany(self::NUMBERS_OF_USERS);

        $this->client->request('GET', '/api/businesses');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = $this->decodeResponse();
        $this->assertGreaterThanOrEqual(self::NUMBERS_OF_USERS, count($data));
    }

    public function testShowAsAdmin(): void
    {
        $business = BusinessFactory::createOne();

        $this->client->request('GET', '/api/businesses/' . $business->getId());

        $this->assertResponseIsSuccessful();

        $data = $this->decodeResponse();
        $this->assertSame($business->getName(), $data['name']);
    }

    public function testCreateAsAdmin(): void
    {
        $payload = [
            'name' => 'newbusiness',
        ];

        $this->requestJson('POST', '/api/businesses', $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->decodeResponse();
        $this->assertSame($payload['name'], $data['name']);
    }

    public function testUpdateAsAdmin(): void
    {
        $business = BusinessFactory::createOne();

        $payload = [
            'name' => 'updatedbusiness',
        ];

        $this->requestJson('PATCH', '/api/businesses/' . $business->getId(), $payload);

        $this->assertResponseIsSuccessful();

        $data = $this->decodeResponse();
        $this->assertSame($payload['name'], $data['name']);
    }

    public function testDeleteAsAdmin(): void
    {
        $business = BusinessFactory::createOne();

        $this->client->request('DELETE', '/api/businesses/' . $business->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/api/businesses/' . $business->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @return array<mixed>
     */
    private function decodeResponse(): array
    {
        $content = $this->client->getResponse()
            ->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'Response content should decode to an array');

        return $data;
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function requestJson(string $method, string $uri, ?array $payload = null): void
    {
        $jsonPayload = $payload !== null ? json_encode($payload) : null;

        $this->assertNotFalse($jsonPayload, 'JSON encoding failed');

        $this->client->request($method, $uri, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonPayload);
    }
}
