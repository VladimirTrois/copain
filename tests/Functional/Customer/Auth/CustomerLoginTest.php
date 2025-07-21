<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Factory\CustomerFactory;

class CustomerLoginTest extends CustomerBaseTestCase
{
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

        return $this->assertContainsMagicLink($email);
    }
}
