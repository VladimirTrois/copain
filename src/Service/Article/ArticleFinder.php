<?php

namespace App\Service\Article;

use App\Entity\Article;
use App\Exception\ArticleNotFoundException;
use App\Exception\BusinessNotFoundException;
use App\Repository\ArticleRepository;
use App\Repository\BusinessRepository;

class ArticleFinder
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private ArticleRepository $articleRepository,
    ) {
    }

    public function listByBusinessId(int $businessId): array
    {
        $business = $this->businessRepository->find($businessId);
        if (!$business) {
            throw new BusinessNotFoundException();
        }

        return $this->articleRepository->findBy(['business' => $business]);
    }

    public function findOneByIdAndBusinessId(int $articleId, int $businessId): ?Article
    {
        $business = $this->businessRepository->find($businessId);
        if (!$business) {
            throw new BusinessNotFoundException();
        }

        $article = $this->articleRepository->findOneBy([
            'id' => $articleId,
            'business' => $business,
        ]);

        if (!$article) {
            throw new ArticleNotFoundException();
        }

        return $article;
    }

    public function findOneBy(array $criteria): ?Article
    {
        $article = $this->articleRepository->findOneBy($criteria);

        if (!$article) {
            throw new ArticleNotFoundException();
        }

        return $article;
    }
}
