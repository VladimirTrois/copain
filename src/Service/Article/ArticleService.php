<?php

namespace App\Service\Article;

use App\Entity\Article;
use App\Entity\Business;

class ArticleService
{
    public function __construct(
        private ArticlePersister $articlePersister,
        private ArticleFinder $articleFinder,
    ) {
    }

    public function find(int|string $id): Article
    {
        return $this->articleFinder->find($id);
    }

    /**
     * @param array<string, mixed> $criteria
     * @return Article[]
     */
    public function findBy(array $criteria): array
    {
        return $this->articleFinder->findBy($criteria);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?Article
    {
        return $this->articleFinder->findOneBy($criteria);
    }

    public function ownerCreateArticleForBusiness(Article $article, Business $business): Article
    {
        $article->setBusiness($business);

        return $this->articlePersister->createArticle($article);
    }

    public function ownerUpdateArticle(Article $article): Article
    {
        return $this->articlePersister->updateArticle($article);
    }

    public function ownerDeleteArticle(int $articleId): void
    {
        $article = $this->articleFinder->findOneBy([
            'id' => $articleId,
        ]);
        $this->articlePersister->deleteArticle($article);
    }
}
