<?php

namespace App\Controller\Article;

use App\Entity\Article;
use App\Service\ArticleService;
use App\Service\Business\BusinessAccessGuard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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

    #[Route('', name: 'article_list', methods: ['GET'])]
    public function list(int $businessId): JsonResponse
    {
        try {
            $articles = $this->articleService->listByBusinessId($businessId);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($articles, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
    public function show(int $businessId, int $id): JsonResponse
    {
        try {
            $article = $this->articleService->findOneByIdAndBusinessId($id, $businessId);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($article, Response::HTTP_OK, [], ['groups' => ['article:read']]);
    }

    #[Route('', name: 'article_create', methods: ['POST'])]
    public function create(int $businessId, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);
            $article = $this->serializer->deserialize($request->getContent(), Article::class, 'json', [
                'groups' => ['article:write'],
            ]);

            $createdArticle = $this->articleService->createArticleForBusiness($article, $business);

            return $this->json($createdArticle, Response::HTTP_CREATED, [], ['groups' => ['article:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'article_update', methods: ['PATCH'])]
    public function update(int $businessId, int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);
            $updatedArticle = $this->articleService->updateArticle($id, $business, $request->getContent());

            return $this->json($updatedArticle, Response::HTTP_OK, [], ['groups' => ['article:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'article_delete', methods: ['DELETE'])]
    public function delete(int $businessId, int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            $business = $this->businessAccessGuard->getBusinessIfUserBelongs($businessId, $user);

            $this->articleService->deleteArticle($id, $business);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
