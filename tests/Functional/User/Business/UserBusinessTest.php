<?php

namespace App\Tests\Functional\User;

use App\Factory\BusinessFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

class UserBusinessTest extends BaseTestCase
{
    public function testUserCanListHisBusinessesById()
    {
        $client = $this->createClientAsUser();
        $numberOfBusinesses = 5;

        $user = UserFactory::find(['email' => self::EMAIL_USER]);
        BusinessFactory::addBusinessesToUser($user, $numberOfBusinesses);

        $client->request('GET', '/api/users/ '.$user->getId().'/businesses');

        $this->assertResponseIsSuccessful();

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
        BusinessFactory::addBusinessesToUser($user, $numberOfBusinesses);

        $client->request('GET', '/api/me/businesses');

        $this->assertResponseIsSuccessful();

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
        BusinessFactory::addBusinessesToUser($user1, $numberOfBusinesses);

        $client->request('GET', '/api/users/'.$user1->getId().'/businesses');

        $this->assertResponseStatusCodeSame(403);
    }
}
