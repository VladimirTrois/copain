<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Tests\BaseTestCase;

final class UserControllerTest extends BaseTestCase
{
    public const NUMBERSOFUSERS = 30;

    public function testIndex(): void
    {
        $clientAdmin = $this->createClientAsAdmin();
        UserFactory::createMany(self::NUMBERSOFUSERS);
        $clientAdmin->request('GET', 'api/users');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($clientAdmin->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFUSERS, $data);
    }
}
