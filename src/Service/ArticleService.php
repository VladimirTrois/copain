<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Business;
use App\Repository\ArticleRepository;
use App\Repository\BusinessRepository;
use App\Service\Article\ArticleFinder;
use App\Service\Article\ArticlePersister;
use App\Service\Business\BusinessAccessGuard;

class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ArticlePersister $articlePersister,
        private ArticleFinder $articleFinder,
        private BusinessAccessGuard $businessAccessGuard,
        private BusinessRepository $businessRepository,
    ) {
    }

    public function publicListByBusinessId(int $businessId): array
    {
        return $this->articleFinder->listByBusinessId($businessId);
    }

    public function publicFindOneByIdAndBusinessId(int $articleId, int $businessId): ?Article
    {
        return $this->articleFinder->findOneByIdAndBusinessId($articleId, $businessId);
    }

    public function ownerCreateArticleForBusiness(Article $article, Business $business): Article
    {
        $article->setBusiness($business);

        return $this->articlePersister->createArticle($article);
    }

    public function ownerUpdateArticle(int $articleId, Business $business, string $json): Article
    {
        $article = $this->articleFinder->findOneByIdAndBusinessId($articleId, $business->getId());

        return $this->articlePersister->updateArticleFromJson($article, $json);
    }

    public function ownerDeleteArticle(int $articleId): void
    {
        $article = $this->articleFinder->findOneBy(['id' => $articleId]);
        $this->articlePersister->deleteArticle($article);
    }
}
