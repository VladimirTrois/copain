<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\BusinessFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

class OrderAccessTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 5;

    public const NUMBERSOFARTICLES = 10;

    public const NUMBERSOFARTICLESMAXPERORDER = 3;

    public function testUserCanListOrdersForTheirBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createOrdersForBusiness($business, self::NUMBERSOFORDERS, self::NUMBERSOFARTICLESMAXPERORDER);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = $this->decodeResponse($client);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFORDERS, count($data));
    }
}
