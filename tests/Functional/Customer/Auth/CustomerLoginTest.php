<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Factory\CustomerFactory;
use Symfony\Component\HttpFoundation\Response;

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

    public function testLoginWithNoEmailFails(): void
    {
        $payload = [];

        $this->client->request(
            'POST',
            '/api/customers/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotFalse($this->client->getResponse()->getContent());
        $this->assertStringContainsString(
            'Please enter your email address.',
            $this->client->getResponse()
                ->getContent()
        );
    }

    public function testLoginWithInvalidEmailFails(): void
    {
        $payload = [
            'email' => 'invalid-email',
        ];

        $this->client->request(
            'POST',
            '/api/customers/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotFalse($this->client->getResponse()->getContent());
        $this->assertStringContainsString(
            'Please enter a valid email address.',
            $this->client->getResponse()
                ->getContent()
        );
    }

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
