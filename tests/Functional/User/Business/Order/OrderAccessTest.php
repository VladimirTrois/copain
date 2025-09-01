<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\BusinessFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $this->assertResponseIsPaginated($data);
        $this->assertIsArray($data['items']);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFORDERS, count($data['items']));
    }

    public function testUserCantListOrdersForOtherBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::createOne();

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserCanShowOrderForTheirBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $order = OrderFactory::createOne([
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders/' . $order->getId());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($order->getId(), $data['id']);
        $this->assertSame($order->getPickUpDate()->format(DATE_ATOM), $data['pickUpDate']);
    }
}
