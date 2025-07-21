<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class CustomerRegisterTest extends CustomerBaseTestCase
{
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
            $this->encodeJson($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $magicLinkUrl = $this->assertContainsMagicLink($email);
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
            $this->encodeJson($payload)
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
            $this->encodeJson($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = $this->getMailerMessage();
        $magicLinkUrl = $this->assertContainsMagicLink($email);
        $redirectUrl = $this->simulateMagicLinkClickAndGetRedirect($magicLinkUrl);

        $this->assertStringStartsWith(self::FRONTEND_BASE_URL, $redirectUrl);

        $queryParams = $this->extractQueryParametersFromUrl($redirectUrl);

        $this->assertArrayHasKey('token', $queryParams);
        $this->assertArrayHasKey('refresh_token', $queryParams);
        $this->assertArrayHasKey('order_token', $queryParams);
    }
}
