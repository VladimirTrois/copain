<?php

namespace App\Tests\Functional\Customer;

use App\Entity\RefreshToken;
use App\Factory\CustomerFactory;
use App\Tests\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomerRefreshTokenTest extends BaseTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    /**
     * Test that a valid refresh token allows JWT refreshing.
     */
    public function testValidRefreshTokenAllowsJwtRefresh(): void
    {
        $customer = CustomerFactory::createOne();

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $jwtManager->create($customer);

        $refreshTokenString = bin2hex(random_bytes(32));

        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken($refreshTokenString);
        $refreshToken->setUsername($customer->getUserIdentifier());
        $refreshToken->setValid((new \DateTimeImmutable())->modify('+1 week'));

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($refreshToken);
        $entityManager->flush();

        $this->verifyRefreshTokenCanRefreshJwt($refreshTokenString);
    }

    /**
     * Test that an invalid refresh token is rejected with 401.
     */
    public function testInvalidRefreshTokenIsRejected(): void
    {
        $this->client->request('POST', '/api/customer/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'refresh_token' => 'invalid-token',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Helper: test the refresh token route works correctly.
     */
    private function verifyRefreshTokenCanRefreshJwt(string $refreshToken): void
    {
        $this->client->request('POST', '/api/customer/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'refresh_token' => $refreshToken,
        ]));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEmpty($responseData['token']);
        $this->assertNotEmpty($responseData['refresh_token']);
    }
}
