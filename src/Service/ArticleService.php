<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Business;
use App\Repository\ArticleRepository;
use App\Repository\BusinessRepository;
use App\Service\Article\ArticlePersister;
use App\Service\Business\BusinessAccessGuard;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ArticlePersister $articlePersister,
        private BusinessAccessGuard $businessAccessGuard,
        private BusinessRepository $businessRepository,
    ) {
    }

    public function listByBusinessId(int $businessId): array
    {
        $business = $this->businessRepository->find($businessId);
        if (!$business) {
            throw new NotFoundHttpException('Business not found.');
        }

        return $this->articleRepository->findBy(['business' => $business]);
    }

    public function findOneByIdAndBusinessId(int $articleId, int $businessId): ?Article
    {
        $business = $this->businessRepository->find($businessId);
        if (!$business) {
            throw new NotFoundHttpException('Business not found.');
        }

        $article = $this->articleRepository->findOneBy([
            'id' => $articleId,
            'business' => $business,
        ]);

        if (!$article) {
            throw new NotFoundHttpException('Article not found.');
        }

        return $article;
    }

    public function listByBusiness(Business $business): array
    {
        return $this->articleRepository->findBy(['business' => $business]);
    }

    public function findOneByIdAndBusiness(int $id, Business $business): Article
    {
        $article = $this->articleRepository->findOneBy(['id' => $id, 'business' => $business]);
        if (!$article) {
            throw new NotFoundHttpException('Article not found.');
        }

        return $article;
    }

    public function createArticleForBusiness(Article $article, Business $business): Article
    {
        $article->setBusiness($business);

        return $this->articlePersister->createArticle($article);
    }

    public function updateArticle(int $id, Business $business, string $json): Article
    {
        $article = $this->findOneByIdAndBusiness($id, $business);

        return $this->articlePersister->updateArticleFromJson($article, $json);
    }

    public function deleteArticle(int $id, Business $business): void
    {
        $article = $this->findOneByIdAndBusiness($id, $business);
        $this->articlePersister->deleteArticle($article);
    }
}
