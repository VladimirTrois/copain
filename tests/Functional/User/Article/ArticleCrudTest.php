<?php

namespace App\Tests\Functional\User\Article;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleCrudTest extends BaseTestCase
{
    public const NUMBERSOFARTICLES = 10;

    public function testOwnerCanCreateArticle(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::addBusinessToUser($user);

        $payload = [
            'name' => 'newArticle',
            'price' => 10,
            'weight' => 10,
            'stock' => 10,
            'rank' => 10,
            'description' => 'descriptionTest',
            'image' => 'imageTest',
            'isAvailable' => true,
        ];

        $client->request(
            'POST',
            '/api/businesses/' . $business->getId() . '/articles',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->decodeResponse($client);
        $this->assertSame($payload['name'], $data['name']);
        $this->assertSame($payload['price'], $data['price']);
        $this->assertSame($payload['weight'], $data['weight']);
        $this->assertSame($payload['stock'], $data['stock']);
        $this->assertSame($payload['rank'], $data['rank']);
        $this->assertSame($payload['description'], $data['description']);
        $this->assertSame($payload['image'], $data['image']);
        $this->assertSame($payload['isAvailable'], $data['isAvailable']);
    }

    public function testNonOwnerCannotCreateArticle(): void
    {
        $client = $this->createClientAsUser();

        $user1 = UserFactory::createOne();
        $business = BusinessFactory::addBusinessToUser($user1);

        $payload = [
            'name' => 'newArticle',
        ];

        $client->request(
            'POST',
            '/api/businesses/' . $business->getId() . '/articles',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateArticle(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::addBusinessToUser($user);
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $payload = [
            'name' => 'updatedArticle',
            'price' => 10,
            'weight' => 10,
            'stock' => 10,
            'rank' => 10,
            'description' => 'descriptionTest',
            'image' => 'imageTest',
            'isAvailable' => true,
        ];

        $client->request(
            'PATCH',
            '/api/businesses/' . $business->getId() . '/articles/' . $article->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame($payload['name'], $data['name']);
        $this->assertSame($payload['price'], $data['price']);
        $this->assertSame($payload['weight'], $data['weight']);
        $this->assertSame($payload['stock'], $data['stock']);
        $this->assertSame($payload['rank'], $data['rank']);
        $this->assertSame($payload['description'], $data['description']);
        $this->assertSame($payload['image'], $data['image']);
        $this->assertSame($payload['isAvailable'], $data['isAvailable']);
    }

    public function testNonOwnerCannotUpdateArticle(): void
    {
        $client = $this->createClientAsUser();

        $user1 = UserFactory::createOne();
        $business = BusinessFactory::addBusinessToUser($user1);
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $payload = [
            'name' => 'updatedArticle',
            'price' => 10,
            'weight' => 10,
            'stock' => 10,
            'rank' => 10,
            'description' => 'descriptionTest',
            'image' => 'imageTest',
            'isAvailable' => true,
        ];

        $client->request(
            'PATCH',
            '/api/businesses/' . $business->getId() . '/articles/' . $article->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $this->encodeJson($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteArticle(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::addBusinessToUser($user);
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $client->request('DELETE', '/api/businesses/' . $business->getId() . '/articles/' . $article->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/articles/' . $article->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
    }

    public function testNonOwnerCannotDeleteArticle(): void
    {
        $client = $this->createClientAsUser();

        $user1 = UserFactory::createOne();
        $business = BusinessFactory::addBusinessToUser($user1);
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $client->request('DELETE', '/api/businesses/' . $business->getId() . '/articles/' . $article->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
