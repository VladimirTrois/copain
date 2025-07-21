<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Entity\RefreshToken;
use App\Factory\CustomerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomerRefreshTokenTest extends CustomerBaseTestCase
{
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
        $jsonPayload = json_encode([
            'refresh_token' => 'invalid-token',
        ]);
        $this->assertNotFalse($jsonPayload, 'JSON encoding failed');

        $this->client->request('POST', '/api/customers/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonPayload);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Helper: test the refresh token route works correctly.
     */
    private function verifyRefreshTokenCanRefreshJwt(string $refreshToken): void
    {
        $jsonPayload = json_encode([
            'refresh_token' => $refreshToken,
        ]);
        $this->assertNotFalse($jsonPayload, 'JSON encoding failed');

        $this->client->request('POST', '/api/customers/token/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonPayload);

        $this->assertResponseIsSuccessful();

        $responseData = $this->decodeResponse($this->client);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEmpty($responseData['token']);
        $this->assertNotEmpty($responseData['refresh_token']);
    }
}
