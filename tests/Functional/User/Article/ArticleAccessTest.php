<?php

namespace App\Tests\Functional\User\Article;

use App\Factory\ArticleFactory;
use App\Factory\BusinessFactory;
use App\Factory\BusinessUserFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleAccessTest extends BaseTestCase
{
    public const NUMBERSOFARTICLES = 10;

    public function testUserCanListArticlesForTheirBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne([
            'user' => $user,
            'business' => $business,
        ]);
        ArticleFactory::createMany(self::NUMBERSOFARTICLES, [
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = $this->decodeResponse($client);
        $this->assertGreaterThanOrEqual(self::NUMBERSOFARTICLES, count($data));
    }

    public function testUserCanShowArticleOfTheirBusiness(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne([
            'user' => $user,
            'business' => $business,
        ]);
        $article = ArticleFactory::createOne([
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/articles/' . $article->getId());

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame($article->getName(), $data['name']);
    }

    public function testListArticlesFromNonexistentBusinessReturnsNotFound(): void
    {
        $client = $this->createClientAsUser();

        $client->request('GET', '/api/businesses/00/articles');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowNonexistentArticleReturnsNotFound(): void
    {
        $client = $this->createClientAsUser();

        $user = UserFactory::find([
            'email' => self::EMAIL_USER,
        ]);
        $business = BusinessFactory::createOne();
        BusinessUserFactory::createOne([
            'user' => $user,
            'business' => $business,
        ]);

        $client->request('GET', '/api/businesses/' . $business->getId() . '/articles/00');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
