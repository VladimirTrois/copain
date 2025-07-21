<?php

namespace App\Service\Article;

use App\Entity\Article;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;

class ArticlePersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private EntityValidator $validator,
    ) {
    }

    public function createArticle(Article $article): Article
    {
        $this->validator->validate($article);

        $this->em->persist($article);
        $this->em->flush();

        return $article;
    }

    public function updateArticle(Article $article): Article
    {
        $this->validator->validate($article);

        $this->em->flush();

        return $article;
    }

    public function deleteArticle(Article $article): void
    {
        $this->em->remove($article);
        $this->em->flush();
    }
}
