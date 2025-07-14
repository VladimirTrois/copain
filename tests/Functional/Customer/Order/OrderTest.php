<?php

namespace App\Tests\Functional\Customer\Order;

use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
use App\Tests\BaseTestCase;

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
}
