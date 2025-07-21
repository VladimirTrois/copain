<?php

namespace App\Tests\Functional\User;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordTest extends BaseTestCase
{
    public function testRequestFailsWhenEmailIsMissing(): void
    {
        $payload = [];

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testRequestSuccessWhenEmailIsUnknownForSecurity(): void
    {
        $client = static::createClient();

        $payload = [
            'email' => 'unknown@example.com',
        ];

        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseIsSuccessful(); // returns 200 even for unknown emails (security reason)
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent(), '{message: Reset email sent if address is valid.}');
    }

    public function testRequestSendsEmailForExistingUser(): void
    {
        $client = $this->createClient();
        $user = UserFactory::createOne();

        $payload = [
            'email' => $user->getEmail(),
        ];

        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailTextBodyContains($email, 'Password Reset');
        $this->assertEmailHtmlBodyContains($email, 'button');
    }

    // --------------------------------------------
    // Reset password
    // --------------------------------------------

    public function testResetFailsWhenTokenAndPasswordAreMissing(): void
    {
        $client = static::createClient();

        $payload = [];

        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload) // missing token and password
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testResetFailsWithInvalidToken(): void
    {
        $client = static::createClient();

        $payload = [
            'token' => 'invalid-token',
            'password' => 'new-password',
        ];

        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testResetSucceedsWithValidToken(): void
    {
        $client = $this->createClient();
        $user = UserFactory::createOne();

        // Generate a real reset token for the user
        $resetPasswordHelper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetPasswordHelper->generateResetToken($user->_real());

        $payload = [
            'token' => $resetToken->getToken(),
            'password' => 'secure-new-password',
        ];

        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertNotFalse($client->getResponse()->getContent());
        $this->assertJson($client->getResponse()->getContent());

        $newPayload = [
            'email' => $user->getEmail(),
            'password' => 'secure-new-password',
        ];

        // Verify password changed and login works
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $this->encodeJson($newPayload));

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('token', $data);
    }
}
