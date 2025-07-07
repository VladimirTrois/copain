<?php

namespace App\Controller\Article;

use App\Entity\Article;
use App\Service\ArticleService;
use App\Service\Business\BusinessAccessGuard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/businesses/{businessId}/articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleService $articleService,
        private BusinessAccessGuard $businessAccessGuard,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', name: 'business_article_list', methods: ['GET'])]
    public function list(int $businessId): JsonResponse
    {
        $articles = $this->articleService->publicListByBusinessId($businessId);

        return $this->json($articles, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('/{id}', name: 'business_article_show', methods: ['GET'])]
    public function show(int $businessId, int $id): JsonResponse
    {
        $article = $this->articleService->publicFindOneByIdAndBusinessId($id, $businessId);

        return $this->json($article, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('', name: 'article_create', methods: ['POST'])]
    public function create(int $businessId, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);
        $article = $this->serializer->deserialize($request->getContent(), Article::class, 'json', [
            'groups' => ['article:write'],
        ]);

        $createdArticle = $this->articleService->ownerCreateArticleForBusiness($article, $business);

        return $this->json($createdArticle, Response::HTTP_CREATED, [], ['groups' => ['article:read']]);
    }

    #[Route('/{id}', name: 'article_update', methods: ['PATCH'])]
    public function update(int $businessId, int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);
        $article = $this->articleService->findArticle(['id' => $id]);

        $this->serializer->deserialize($request->getContent(), Article::class, 'json', [
            'object_to_populate' => $article,
            'groups' => ['article:write'],
        ]);

        $updatedArticle = $this->articleService->ownerUpdateArticle($article, $business, $request->getContent());

        return $this->json($updatedArticle, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('/{id}', name: 'article_delete', methods: ['DELETE'])]
    public function delete(int $businessId, int $id): JsonResponse
    {
        $user = $this->getUser();
        $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);
        $this->articleService->ownerDeleteArticle($id, $business);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
