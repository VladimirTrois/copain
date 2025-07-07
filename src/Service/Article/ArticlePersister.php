<?php

namespace App\Service\Article;

use App\Entity\Article;
use App\Service\EntityValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ArticlePersister
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
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

    public function updateArticleFromJson(Article $article, string $json): Article
    {
        $this->serializer->deserialize($json, Article::class, 'json', [
            'object_to_populate' => $article,
            'groups' => ['article:write'],
        ]);

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
