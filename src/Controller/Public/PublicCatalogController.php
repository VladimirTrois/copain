<?php

namespace App\Controller\Public;

use App\Service\Article\ArticleService;
use App\Service\Business\BusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/public/businesses/{businessId}/articles', name: 'public_business_article_')]
class PublicCatalogController extends AbstractController
{
    public function __construct(
        private ArticleService $articleService,
        private BusinessService $businessService,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(int $businessId): JsonResponse
    {
        $business = $this->businessService->find($businessId);
        $articles = $this->articleService->findBy(['business' => $business]);

        return $this->json($articles, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('/{articleId}', name: 'show', methods: ['GET'])]
    public function show(int $businessId, int $articleId): JsonResponse
    {
        $business = $this->businessService->find($businessId);
        $article = $this->articleService->findOneBy(['id' => $articleId, 'business' => $business]);

        return $this->json($article, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }
}
