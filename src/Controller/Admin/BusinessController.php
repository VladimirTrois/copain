<?php

// src/Controller/BusinessController.php

namespace App\Controller\Admin;

use App\Repository\BusinessRepository;
use App\Repository\BusinessUserRepository;
use App\Service\BusinessManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/api/businesses', name: 'api_businesses_')]
class BusinessController extends AbstractController
{
    public function __construct(
        private BusinessManager $businessManager,
        private BusinessRepository $businessRepository,
        private BusinessUserRepository $businessUserRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $businesses = $this->businessRepository->findAll();

        return $this->json($businesses, 200, [], ['groups' => ['business:list']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $business = $this->businessRepository->find($id);
        if (!$business) {
            return $this->json(['error' => 'business not found'], 404);
        }

        return $this->json($business, 200, [], ['groups' => ['business:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $business = $this->businessManager->createFromJson($request->getContent());

            return $this->json($business, 201, [], ['groups' => ['business:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $business = $this->businessRepository->find($id);
        if (!$business) {
            return $this->json(['error' => 'business not found'], 404);
        }

        try {
            $updatedbusiness = $this->businessManager->updateFromJson($business, $request->getContent());

            return $this->json($updatedbusiness, 200, [], ['groups' => ['business:read']]);
        } catch (UnprocessableEntityHttpException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => json_decode($e->getMessage())], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input', 'details' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $business = $this->businessRepository->find($id);
        if (!$business) {
            return $this->json(['error' => 'business not found'], 404);
        }

        $this->businessManager->delete($business);

        // 204 No Content is appropriate for successful deletes without body
        return $this->json(null, 204);
    }
}
