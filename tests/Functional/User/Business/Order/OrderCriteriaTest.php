<?php

namespace App\Tests\Functional\User\Business\Order;

use App\Factory\BusinessFactory;
use App\Factory\CustomerFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderCriteriaTest extends BaseTestCase
{
    public const NUMBERSOFORDERS = 10;

    public const NUMBERSOFARTICLES = 10;

    public const NUMBERSOFARTICLESMAXPERORDER = 3;

    public function testFilterByPickUpDate(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $pickUpDate = new \DateTime('now');
        $numbersOfOrders = self::NUMBERSOFORDERS;
        $pickUpDateTomorrow = new \DateTime('tomorrow');
        $numbersOfOrdersTomorrow = self::NUMBERSOFORDERS + 10;

        OrderFactory::createMany($numbersOfOrders, [
            'business' => $business,
            'pickUpDate' => $pickUpDate,
        ]);

        OrderFactory::createMany($numbersOfOrdersTomorrow, [
            'business' => $business,
            'pickUpDate' => $pickUpDateTomorrow,
        ]);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpDate=' . $pickUpDate->format('Y-m-d')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrders, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpDate=' . $pickUpDateTomorrow->format('Y-m-d')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersTomorrow, $data);
    }

    public function testFilterByPickUpFrom(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $dateTimeNow = new \DateTime('now');
        $dateTimePlusOneHour = new \DateTime('+1 hour');
        $dateTimePlusTwoHour = new \DateTime('+2 hour');

        $numbersOfOrdersNow = self::NUMBERSOFORDERS;
        $numbersOfOrdersPlusOneHour = self::NUMBERSOFORDERS;
        $numbersOfOrdersPlusTwoHour = self::NUMBERSOFORDERS;

        OrderFactory::createMany($numbersOfOrdersNow, [
            'business' => $business,
            'pickUpDate' => $dateTimeNow,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusOneHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusOneHour,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusTwoHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusTwoHour,
        ]);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpFrom=' . $dateTimeNow->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersNow + $numbersOfOrdersPlusOneHour + $numbersOfOrdersPlusTwoHour, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpFrom=' . $dateTimePlusOneHour->format(
                'Y-m-d-H:i:s'
            )
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersPlusOneHour + $numbersOfOrdersPlusTwoHour, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpFrom=' . $dateTimePlusTwoHour->format(
                'Y-m-d-H:i:s'
            )
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersPlusTwoHour, $data);
    }

    public function testFilterByPickUpTo(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $dateTimeNow = new \DateTime('now');
        $dateTimePlusOneHour = new \DateTime('+1 hour');
        $dateTimePlusTwoHour = new \DateTime('+2 hour');

        $numbersOfOrdersNow = self::NUMBERSOFORDERS;
        $numbersOfOrdersPlusOneHour = self::NUMBERSOFORDERS;
        $numbersOfOrdersPlusTwoHour = self::NUMBERSOFORDERS;

        OrderFactory::createMany($numbersOfOrdersNow, [
            'business' => $business,
            'pickUpDate' => $dateTimeNow,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusOneHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusOneHour,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusTwoHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusTwoHour,
        ]);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpTo=' . $dateTimeNow->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersNow, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpTo=' . $dateTimePlusOneHour->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersNow + $numbersOfOrdersPlusOneHour, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpTo=' . $dateTimePlusTwoHour->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersNow + $numbersOfOrdersPlusOneHour + $numbersOfOrdersPlusTwoHour, $data);
    }

    public function testFilterByPickUpToAndPickUpFrom(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $dateTimeNow = new \DateTime('now');
        $dateTimePlusOneHour = new \DateTime('+1 hour');
        $dateTimePlusTwoHour = new \DateTime('+2 hour');

        $numbersOfOrdersNow = self::NUMBERSOFORDERS + 2;
        $numbersOfOrdersPlusOneHour = self::NUMBERSOFORDERS + 5;
        $numbersOfOrdersPlusTwoHour = self::NUMBERSOFORDERS + 3;

        OrderFactory::createMany($numbersOfOrdersNow, [
            'business' => $business,
            'pickUpDate' => $dateTimeNow,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusOneHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusOneHour,
        ]);

        OrderFactory::createMany($numbersOfOrdersPlusTwoHour, [
            'business' => $business,
            'pickUpDate' => $dateTimePlusTwoHour,
        ]);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpFrom=' . $dateTimeNow->format(
                'Y-m-d-H:i:s'
            ) . '&pickUpTo=' . $dateTimePlusOneHour->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersNow + $numbersOfOrdersPlusOneHour, $data);

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?pickUpFrom=' . $dateTimePlusOneHour->format(
                'Y-m-d-H:i:s'
            ) . '&pickUpTo=' . $dateTimePlusTwoHour->format('Y-m-d-H:i:s')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfOrdersPlusOneHour + $numbersOfOrdersPlusTwoHour, $data);
    }

    public function testFilterByIsPickedUp(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isPickedUp' => false,
        ]);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isPickedUp' => true,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?isPickedUp=true');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);
    }

    public function testFilterByIsValidatedByCustomer(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isValidatedByCustomer' => false,
        ]);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isValidatedByCustomer' => true,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?isValidatedByCustomer=true');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);
    }

    public function testFilterByIsValidatedByBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isValidatedByBusiness' => false,
        ]);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
            'isValidatedByBusiness' => true,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?isValidatedByBusiness=true');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);
    }

    public function testFilterByCustomerFirstNameOrLastName(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        $numbersOfJohnDoeOrders = self::NUMBERSOFORDERS + 3;
        $numbersOfJaneBoeOrders = self::NUMBERSOFORDERS + 5;

        OrderFactory::createMany($numbersOfJohnDoeOrders, [
            'business' => $business,
            'customer' => CustomerFactory::createOne([
                'firstName' => 'John',
                'lastName' => 'Doe',
            ]),
        ]);

        OrderFactory::createMany($numbersOfJaneBoeOrders, [
            'business' => $business,
            'customer' => CustomerFactory::createOne([
                'firstName' => 'Jane',
                'lastName' => 'Boe',
            ]),
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?customerFirstName=John');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfJohnDoeOrders, $data);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?customerLastName=Boe');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount($numbersOfJaneBoeOrders, $data);
    }

    public function testOrderByPickUpDate(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?orderBy=pickUpDate');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted ascending by pickUpDate
        $dates = array_column($data, 'pickUpDate');
        for ($i = 1; $i < count($dates); $i++) {
            $this->assertLessThanOrEqual($dates[$i], $dates[$i - 1], 'Array not sorted ascending by pickUpDate');
        }

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?orderBy=pickUpDate&orderDir=desc');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted descending by pickUpDate
        $dates = array_column($data, 'pickUpDate');
        for ($i = 1; $i < count($dates); $i++) {
            $this->assertGreaterThanOrEqual($dates[$i], $dates[$i - 1], 'Array not sorted descending by pickUpDate');
        }
    }

    public function testOrderByCustomerFirstName(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?orderBy=customerFirstName');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted ascending by customerFirstName
        $firstNames = array_column($data, 'customerFirstName');
        for ($i = 1; $i < count($firstNames); $i++) {
            $this->assertLessThanOrEqual(
                $firstNames[$i],
                $firstNames[$i - 1],
                'Array not sorted ascending by customerFirstName'
            );
        }

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?orderBy=customerFirstName&orderDir=desc'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted descending by customerFirstName
        $firstNames = array_column($data, 'customerFirstName');
        for ($i = 1; $i < count($firstNames); $i++) {
            $this->assertGreaterThanOrEqual(
                $firstNames[$i],
                $firstNames[$i - 1],
                'Array not sorted descending by customerFirstName'
            );
        }
    }

    public function testOrderByCustomerLastName(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);

        $business = BusinessFactory::addBusinessToUser($user);

        OrderFactory::createMany(self::NUMBERSOFORDERS, [
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/orders?orderBy=customerLastName');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted ascending by customerLastName
        $lastNames = array_column($data, 'customerLastName');
        for ($i = 1; $i < count($lastNames); $i++) {
            $this->assertLessThanOrEqual(
                $lastNames[$i],
                $lastNames[$i - 1],
                'Array not sorted ascending by customerLastName'
            );
        }

        $client->request(
            'GET',
            '/api/businesses/' . $business->getId() . '/orders?orderBy=customerLastName&orderDir=desc'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->decodeResponse($client);
        $this->assertCount(self::NUMBERSOFORDERS, $data);

        // Check if sorted descending by customerLastName
        $lastNames = array_column($data, 'customerLastName');
        for ($i = 1; $i < count($lastNames); $i++) {
            $this->assertGreaterThanOrEqual(
                $lastNames[$i],
                $lastNames[$i - 1],
                'Array not sorted descending by customerLastName'
            );
        }
    }
}
