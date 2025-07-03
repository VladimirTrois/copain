<?php

namespace App\Tests\Functional;

use App\Factory\BusinessFactory;
use App\Factory\BusinessUserFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

final class UserTest extends BaseTestCase
{
    public function testUserCanListHisBusinessesById()
    {
        $client = $this->createClientAsUser();
        $numberOfBusinesses = 5;

        $user = UserFactory::find(['email' => self::EMAIL_USER]);
        $businesses = BusinessFactory::createMany($numberOfBusinesses);
        foreach ($businesses as $business) {
            BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);
        }

        $client->request('GET', '/api/users/ '.$user->getId().'/businesses');

        $this->assertResponseIsSuccessful(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfBusinesses, $data);
        foreach ($data as $business_data) {
            $this->assertArrayHasKey('id', $business_data);
            $this->assertArrayHasKey('name', $business_data);
            $this->assertArrayHasKey('responsibilities', $business_data);
        }
    }

    public function testUserCanListHisBusinessesViaMeEndpoint()
    {
        $client = $this->createClientAsUser();
        $numberOfBusinesses = 5;

        $user = UserFactory::find(['email' => self::EMAIL_USER]);
        $businesses = BusinessFactory::createMany($numberOfBusinesses);
        foreach ($businesses as $business) {
            $businessUser = BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);
        }

        $client->request('GET', '/api/me/businesses');

        $this->assertResponseIsSuccessful(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfBusinesses, $data);
        foreach ($data as $business_data) {
            $this->assertArrayHasKey('id', $business_data);
            $this->assertArrayHasKey('name', $business_data);
            $this->assertArrayHasKey('responsibilities', $business_data);
        }
    }

    public function testUserCannotListAnotherUsersBusinesses()
    {
        $client = $this->createClientAsUser();
        $numberOfBusinesses = 5;

        $user1 = UserFactory::createOne();
        $businesses = BusinessFactory::createMany($numberOfBusinesses);
        foreach ($businesses as $business) {
            BusinessUserFactory::createOne(['user' => $user1, 'business' => $business, 'responsibilities' => ['owner']]);
        }

        $client->request('GET', '/api/users/'.$user1->getId().'/businesses');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOwnerCanListAllUsersInHisBusiness()
    {
        $client = $this->createClientAsUser();
        $numberOfEmployees = 5;

        $user = UserFactory::find(['email' => self::EMAIL_USER]);
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);
        $otherUsers = UserFactory::createMany($numberOfEmployees);
        foreach ($otherUsers as $otherUser) {
            BusinessUserFactory::createOne(['user' => $otherUser, 'business' => $business, 'responsibilities' => ['employee']]);
        }

        $client->request('GET', '/api/businesses/'.$business->getId().'/users');

        $this->assertResponseIsSuccessful(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($numberOfEmployees + 1, $data);
        foreach ($data as $user_data) {
            $this->assertArrayHasKey('id', $user_data);
            $this->assertArrayHasKey('email', $user_data);
        }
    }

    public function testNonOwnerCannotListUsersInBusiness()
    {
        $client = $this->createClientAsUser();
        $numberOfEmployees = 5;

        $user = UserFactory::createOne();
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);
        $otherUsers = UserFactory::createMany($numberOfEmployees);
        foreach ($otherUsers as $otherUser) {
            BusinessUserFactory::createOne(['user' => $otherUser, 'business' => $business, 'responsibilities' => ['employee']]);
        }

        $client->request('GET', '/api/businesses/'.$business->getId().'/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOwnerCanAddUserToHisBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find(['email' => self::EMAIL_USER]);
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne(['user' => $user, 'business' => $business, 'responsibilities' => ['owner']]);

        $newUser = UserFactory::createOne();

        $payload = [
            'email' => $newUser->getEmail(),
            'responsibilities' => ['employee'],
        ];

        $client->request('POST', '/api/businesses/'.$business->getId().'/users', [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful(200);
    }
}
