<?php

namespace App\Tests\Functional\Customer;

use App\Factory\CustomerFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerMagicLinkTest extends BaseTestCase
{
    public const FRONTEND_BASE_URL = 'https://test.com';

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    /**
     * Test that a customer can request a magic link to login,
     * follow it, and be redirected with JWT and refresh token.
     */
    public function testMagicLinkLoginRedirectsWithTokens(): void
    {
        $customer = CustomerFactory::createOne();

        $magicLinkUrl = $this->sendLoginRequestAndGetMagicLink($customer->getEmail());
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
    }

    /**
     * Test that when an order token is provided in login,
     * it is preserved and included in the final redirect URL.
     */
    public function testMagicLinkRedirectIncludesOrderToken(): void
    {
        $customer = CustomerFactory::createOne();
        $orderToken = bin2hex(random_bytes(16));

        $magicLinkUrl = $this->sendLoginRequestAndGetMagicLink($customer->getEmail(), ['order_token' => $orderToken]);
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
        $this->assertArrayHasKey('order_token', $queryParams);
        $this->assertSame($orderToken, $queryParams['order_token']);
    }

    /**
     * Helper: send login request with email (+optional extra parameters)
     * and extract magic login URL from the sent email.
     */
    private function sendLoginRequestAndGetMagicLink(string $email, array $extraParams = []): string
    {
        $payload = array_merge(['email' => $email], $extraParams);

        $this->client->request(
            'POST',
            '/api/customer/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var \Symfony\Bridge\Twig\Mime\TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $htmlBody = $email->getHtmlBody();

        preg_match('/https?:\/\/[^\s"]+/', $htmlBody, $matches);
        $this->assertNotEmpty($matches, 'No URL found in email body');

        return $matches[0];
    }

    /**
     * Helper: simulate clicking the magic link and return the redirect location.
     */
    private function simulateMagicLinkClickAndGetRedirect(string $magicLinkUrl): string
    {
        $parsedUrl = parse_url($magicLinkUrl);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $magicLinkPath = $path.$query;

        $this->client->followRedirects(false);
        $this->client->request('GET', $magicLinkPath);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        return $this->client->getResponse()->headers->get('Location');
    }

    /**
     * Helper: parse a URL and return its query parameters as an array.
     */
    private function extractQueryParametersFromUrl(string $url): array
    {
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        return $queryParams;
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
