<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Factory\CustomerFactory;
use App\Tests\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class CustomerLoginTest extends BaseTestCase
{
    public const FRONTEND_BASE_URL = 'https://test.com';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    public function testLoginRedirectsWithTokens(): void
    {
        $customer = CustomerFactory::createOne();

        $magicLinkUrl = $this->sendLoginRequestAndGetMagicLink($customer->getEmail());
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
    }

    public function testLoginWithOrderTokenRedirectsWithOrderToken(): void
    {
        $customer = CustomerFactory::createOne();
        $orderToken = bin2hex(random_bytes(16));

        $magicLinkUrl = $this->sendLoginRequestAndGetMagicLink($customer->getEmail(), [
            'order_token' => $orderToken,
        ]);
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
        $this->assertArrayHasKey('order_token', $queryParams);
        $this->assertSame($orderToken, $queryParams['order_token']);
    }

    // public function testSecondLoginWithSameLing(): void
    // {
    //     $customer = CustomerFactory::createOne();

    //     $magicLinkUrl = $this->sendLoginRequestAndGetMagicLink($customer->getEmail());
    //     $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);
    //     $redirectUrl2 = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

    //     $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl2);

    //     $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

    //     $this->assertArrayHasKey('token', $queryParams);
    //     $this->assertArrayHasKey('refresh_token', $queryParams);
    // }

    /**
     * @param string[] $extraParams
     */
    private function sendLoginRequestAndGetMagicLink(string $email, array $extraParams = []): string
    {
        $payload = array_merge([
            'email' => $email,
        ], $extraParams);

        $jsonPayload = json_encode($payload);
        $this->assertNotFalse($jsonPayload, 'JSON encoding failed');

        $this->client->request(
            'POST',
            '/api/customers/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonPayload
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var \Symfony\Bridge\Twig\Mime\TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $htmlBody = $email->getHtmlBody();

        $this->assertIsString($htmlBody, 'Email HTML body must be a string');

        preg_match('/https?:\/\/[^\s"]+/', $htmlBody, $matches);
        $this->assertNotEmpty($matches, 'No URL found in email body');

        return $matches[0] ?? throw new \RuntimeException('No URL found in email body');
    }

    private function simulateMagicLinkClickAndGetRedirect(string $magicLinkUrl): string
    {
        $parsedUrl = parse_url($magicLinkUrl);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $magicLinkPath = $path . $query;

        $this->client->followRedirects(false);
        $this->client->request('GET', $magicLinkPath);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        return $this->client->getResponse()
            ->headers->get('Location');
    }

    /**
     * @return array<mixed>
     */
    private function extractQueryParametersFromUrl(string $url): array
    {
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        return $queryParams;
    }
}
