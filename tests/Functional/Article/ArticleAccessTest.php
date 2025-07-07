<?php

namespace App\Tests\Functional\Article;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleAccessTest extends BaseTestCase
{
    public const NUMBERSOFARTICLES = 10;

    public function testListArticlesFromBusiness(): void
    {
        $client = $this->createClientAsUser();

        $business = BusinessFactory::createOne();
        ArticleFactory::createMany(self::NUMBERSOFARTICLES, ['business' => $business]);

        $client->request('GET', '/api/businesses/'.$business->getId().'/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFARTICLES, count($data));
    }

    public function testShow(): void
    {
        $client = $this->createClientAsUser();

        $business = BusinessFactory::createOne();
        $article = ArticleFactory::createOne(['business' => $business]);

        $client->request('GET', '/api/businesses/'.$business->getId().'/articles/'.$article->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($article->getName(), $data['name']);
    }

    public function testListArticlesFromBusinessInexistent(): void
    {
        $client = $this->createClientAsUser();

        $client->request('GET', '/api/businesses/00/articles');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowArticleInexistent(): void
    {
        $client = $this->createClientAsUser();

        $business = BusinessFactory::createOne();

        $client->request('GET', '/api/businesses/'.$business->getId().'/articles/00');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
