<?php

namespace App\Tests\Functional\Customer\Order;

use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
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
}
