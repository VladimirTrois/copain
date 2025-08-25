<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderUpdateTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 1;

    public const NUMBERSOFARTICLES = 5;

    public const NUMBERSOFNEWARTICLES = 4;

    public function testBusinessCanUpdateOrder(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        //Create order to business with random numper of articles
        $order = OrderFactory::createOrdersForBusiness($business, 1, self::NUMBERSOFARTICLES);

        $newArticles = ArticleFactory::createMany(self::NUMBERSOFNEWARTICLES, [
            'business' => $business,
        ]);

        $items = [];
        foreach ($newArticles as $newArticle) {
            $items = array_merge($items, [
                [
                    'articleId' => $newArticle->getId(),
                    'quantity' => rand(1, 5),
                ],
            ]);
        }

        $payload = [
            'pickUpDate' => '2025-11-30',
            'items' => $items,
        ];

        $client->request(
            'PATCH',
            '/api/businesses/' . $business->getId() . '/orders/' . $order[0]->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        //Verify that order has been updated with new articles
        $order = $order[0];

        $this->assertCount(self::NUMBERSOFNEWARTICLES, $order->getOrderItems());
        $orderItems = $order->getOrderItems();

        $newItems = [];
        foreach ($orderItems as $orderItem) {
            $newItems = array_merge($newItems, [
                [
                    'articleId' => $orderItem->getArticle()
                        ->getId(),
                    'quantity' => $orderItem->getQuantity(),
                ],
            ]);
        }
        $this->assertEquals($items, $newItems);
    }
}
