<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\BusinessFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

class OrderPaginationTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 30;

    public const NUMBERSOFARTICLESMAXPERORDER = 3;

    public function testUserCanListOrdersWithPagination(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createOrdersForBusiness($business, self::NUMBERSOFORDERS, self::NUMBERSOFARTICLESMAXPERORDER);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?limit=5');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = $this->decodeResponse($client);
        $this->assertResponseIsPaginated($data);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?limit=10&page=2');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = $this->decodeResponse($client);
        $this->assertResponseIsPaginated($data);
    }
}
