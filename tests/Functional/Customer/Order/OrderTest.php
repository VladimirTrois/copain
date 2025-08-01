<?php

namespace App\Tests\Functional\Customer\Order;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 10;

    public function testListOrders(): void
    {
        $client = $this->createClientAsCustomer();

        $customer = CustomerFactory::find(['email' => self::EMAIL_CUSTOMER]);

        OrderFactory::createMany(self::NUMBERSOFORDERS, ['customer' => $customer]);

        $client->request('GET', '/api/customers/orders');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(self::NUMBERSOFORDERS, $data);
    }

    public function testShowOrder(): void
    {
        $client = $this->createClientAsCustomer();

        $customer = CustomerFactory::find(['email' => self::EMAIL_CUSTOMER]);

        $order = OrderFactory::createOne(['customer' => $customer]);

        $client->request('GET', '/api/customers/orders/'.$order->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testNotFoundOnGetAnotherCustomerOrder(): void
    {
        $client = $this->createClientAsCustomer();

        $customer1 = CustomerFactory::createOne();

        $order = OrderFactory::createOne(['customer' => $customer1]);

        $client->request('GET', '/api/customers/orders/'.$order->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateOrder(): void
    {
        $client = $this->createClientAsCustomer();

        $business = BusinessFactory::createOne();
        $article = ArticleFactory::createOne(['business' => $business]);

        $payload = [
            'businessId' => $business->getId(),
            'pickUpDate' => '2025-11-30',
            'items' => [
                [
                    'articleId' => $article->getId(),
                    'quantity' => 1,
                ],
            ],
        ];

        $client->request(
            'POST',
            '/api/customers/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);

        // Assert the created order exists
        $order = OrderFactory::find(['id' => $responseData['id']]);
        $this->assertNotNull($order);
        $this->assertEquals($responseData['id'], $order->getId());
        $this->assertEquals($payload['pickUpDate'], $order->getPickUpDate()->format('Y-m-d'));
        $orderItem = $order->getOrderItems()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($article->getId(), $orderItem->getArticle()->getId());
        $this->assertEquals($payload['items'][0]['quantity'], $orderItem->getQuantity());
    }

    public function testCreateOrderFailsWithMissingFields(): void
    {
        $client = $this->createClientAsCustomer();

        $client->request(
            'POST',
            '/api/customers/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['businessId' => '', 'pickUpDate' => '', 'items' => []])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateOrderFailsWithInvalidFields(): void
    {
        $client = $this->createClientAsCustomer();

        $client->request(
            'POST',
            '/api/customers/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['businessId' => 'invalid-id', 'items' => []])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateOrderFailsWithDuplicateArticles(): void
    {
        $client = static::createClientAsCustomer();

        $business = BusinessFactory::createOne();
        $article = ArticleFactory::createOne(['business' => $business]);

        $payload = [
            'businessId' => $business->getId(),
            'pickUpDate' => '2025-11-30',
            'items' => [
                ['articleId' => $article->getId(), 'quantity' => 2],
                ['articleId' => $article->getId(), 'quantity' => 3],
            ],
        ];

        $client->request('POST', '/api/customers/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertStringContainsString('Duplicate articleId', json_encode($response));
    }

    public function testCreateOrderFailsWithArticleFromAnotherBusiness(): void
    {
        $client = $this->createClientAsCustomer();

        $business1 = BusinessFactory::createOne();
        $business2 = BusinessFactory::createOne();

        $article1 = ArticleFactory::createOne(['business' => $business1]);
        $article2 = ArticleFactory::createOne(['business' => $business2]);

        $payload = [
            'businessId' => $business1->getId(),
            'pickUpDate' => '2025-11-30',
            'items' => [
                ['articleId' => $article1->getId(), 'quantity' => 1],
                ['articleId' => $article2->getId(), 'quantity' => 1],
            ],
        ];

        $client->request('POST', '/api/customers/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('An article is not from the business.', json_encode($response));
    }

    public function testUpdateOrder(): void
    {
        $client = $this->createClientAsCustomer();

        $customer = CustomerFactory::find(['email' => self::EMAIL_CUSTOMER]);

        $business = BusinessFactory::createOne();
        $oldArticle = ArticleFactory::createOne(['business' => $business]);
        $newArticle = ArticleFactory::createOne(['business' => $business]);
        $order = OrderFactory::createOne(['customer' => $customer, 'business' => $business]);
        OrderItemFactory::createOne(['order' => $order, 'article' => $oldArticle]);

        $payload = [
            'pickUpDate' => '2025-11-30',
            'items' => [
                [
                    'articleId' => $newArticle->getId(),
                    'quantity' => 1,
                ],
            ],
        ];

        $client->request(
            'PATCH',
            '/api/customers/orders/'.$order->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $order = OrderFactory::find(['id' => $order->getId()]);
        $orderItem = $order->getOrderItems()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($newArticle->getId(), $orderItem->getArticle()->getId());
        $this->assertEquals($payload['items'][0]['quantity'], $orderItem->getQuantity());
    }
}
