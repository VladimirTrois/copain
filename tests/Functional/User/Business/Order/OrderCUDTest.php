<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderCUDTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 10;

    public const NUMBERSOFARTICLES = 10;

    public function testUserCanCreateOrder(): void
    {
        $this->markTestSkipped('Not implemented yet');
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::addBusinessToUser($user);
        $articles = ArticleFactory::createMany(self::NUMBERSOFARTICLES, [
            'business' => $business,
        ]);

        $payload = [
            'businessId' => $business->getId(),
            'pickUpDate' => '2025-11-30',
            'items' => [
                [
                    'articleId' => $articles[0]->getId(),
                    'quantity' => 1,
                ],
            ],
        ];

        $client->request(
            'POST',
            '/api/businesses/' . $business->getId() . '/orders',
            [],
            [],
            [],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->decodeResponse($client);

        $this->assertSame($payload['pickUpDate'], $data['pickUpDate']);
        $this->assertSame($payload['businessId'], $data['business']['id']);
        $this->assertSame($payload['items'][0]['articleId'], $data['items'][0]['article']['id']);
        $this->assertSame($payload['items'][0]['quantity'], $data['items'][0]['quantity']);
    }
}
