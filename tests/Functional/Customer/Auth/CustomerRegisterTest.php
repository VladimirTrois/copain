<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerRegisterTest extends BaseTestCase
{
    public const FRONTEND_BASE_URL = 'https://test.com';

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    public function testRegisterRedirectsWithTokens(): void
    {
        $payload = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'Bt9wW@example.com',
            'phone' => '1234567890',
        ];

        $this->client->request(
            'POST',
            '/api/customers/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var \Symfony\Bridge\Twig\Mime\TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $htmlBody = $email->getHtmlBody();

        preg_match('/https?:\/\/[^\s"]+/', $htmlBody, $matches);
        $this->assertNotEmpty($matches, 'No URL found in email body');

        $magicLinkUrl = $matches[0];
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
    }

    public function testRegisterExistingEmailReturnsSuccess(): void
    {
        $customer = CustomerFactory::createOne();

        $payload = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $customer->getEmail(),
            'phone' => '1234567890',
        ];

        $this->client->request(
            'POST',
            '/api/customers/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(0);
    }

    public function testPlaceOrderRedirectsWithTokens(): void
    {
        $business = BusinessFactory::createOne();
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $payload = [
            'customer' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'customerregister2@example.com',
                'phone' => '1234567890',
            ],
            'order' => [
                'businessId' => $business->getId(),
                'pickUpDate' => '2025-11-30',
                'items' => [
                    [
                        'articleId' => $article->getId(),
                        'quantity' => 1,
                    ],
                ],
            ],
        ];

        $this->client->request(
            'POST',
            '/api/customers/public/orders',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var \Symfony\Bridge\Twig\Mime\TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $htmlBody = $email->getHtmlBody();

        preg_match('/https?:\/\/[^\s"]+/', $htmlBody, $matches);
        $this->assertNotEmpty($matches, 'No URL found in email body');

        $magicLinkUrl = $matches[0];
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
        $this->assertArrayHasKey('order_token', $queryParams);
    }

    private function sendLoginRequestAndGetMagicLink(string $email, array $extraParams = []): string
    {
        $payload = array_merge([
            'email' => $email,
        ], $extraParams);

        $this->client->request(
            'POST',
            '/api/customers/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
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

    private function extractQueryParametersFromUrl(string $url): array
    {
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        return $queryParams;
    }
}
