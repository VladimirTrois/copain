<?php

namespace App\Service\Article;

use App\Entity\Article;
use App\Exception\ArticleNotFoundException;
use App\Repository\ArticleRepository;
use App\Repository\BusinessRepository;

class ArticleFinder
{
    public function __construct(
        private BusinessRepository $businessRepository,
        private ArticleRepository $articleRepository,
    ) {
    }

    public function find(int $id): Article
    {
        $article = $this->articleRepository->find($id);
        if (! $article) {
            throw new ArticleNotFoundException();
        }

        return $article;
    }

    public function findBy(array $criteria): array
    {
        $articles = $this->articleRepository->findBy($criteria);
        if (! $articles) {
            throw new ArticleNotFoundException();
        }

        return $articles;
    }

    public function findOneBy(array $criteria): ?Article
    {
        $article = $this->articleRepository->findOneBy($criteria);

        if (! $article) {
            throw new ArticleNotFoundException();
        }

        return $article;
    }
}
