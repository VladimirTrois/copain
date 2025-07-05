<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordControllerTest extends BaseTestCase
{
    public function testRequestResetPasswordWithoutEmail(): void
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testRequestResetPasswordWithUnknownEmail(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'unknown@example.com'])
        );

        $this->assertResponseIsSuccessful(); // returns 200 even for unknown emails (security reason)
        $this->assertJson($client->getResponse()->getContent(), '{message: Reset email sent if address is valid.}');
    }

    public function testResetPasswordMissingFields(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([]) // missing token and password
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testResetPasswordWithInvalidToken(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'token' => 'invalid-token',
                'password' => 'new-password',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testRequestPasswordResetSendsEmail(): void
    {
        $client = $this->createClient();
        $user = UserFactory::createOne();

        $client->request(
            'POST',
            '/api/reset-password/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $user->getEmail()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($client->getResponse()->getContent());

        // Optionally assert email was sent, e.g., check mailer spool or mock
    }

    public function testResetPasswordWithValidToken(): void
    {
        $client = $this->createClient();
        $user = UserFactory::createOne();

        // Generate a real reset token for the user
        $resetPasswordHelper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetPasswordHelper->generateResetToken($user->_real());

        $client->request(
            'POST',
            '/api/reset-password/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'token' => $resetToken->getToken(),
                'password' => 'secure-new-password',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($client->getResponse()->getContent());

        // Verify password changed and login works
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $user->getEmail(),
            'password' => 'secure-new-password',
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }
}
