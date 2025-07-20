<?php

// src/Controller/BusinessController.php

namespace App\Controller\Admin;

use App\Entity\Business;
use App\Service\Business\BusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_USER')]
#[Route('/api/businesses', name: 'api_businesses_')]
class BusinessController extends AbstractController
{
    public function __construct(
        private BusinessService $businessService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $businesses = $this->businessService->listAll();

        return $this->json($businesses, Response::HTTP_OK, [], [
            'groups' => ['business:list'],
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $business = $this->businessService->find($id);

        return $this->json($business, Response::HTTP_OK, [], [
            'groups' => ['business:read'],
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $business = $this->serializer->deserialize($request->getContent(), Business::class, 'json', [
            'groups' => ['business:write'],
        ]);

        $business = $this->businessService->createBusiness($business);

        return $this->json($business, Response::HTTP_CREATED, [], [
            'groups' => ['business:read'],
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $business = $this->businessService->find($id);

        // Deserialize into existing entity (Symfony will hydrate object)
        $this->serializer->deserialize(
            $request->getContent(),
            Business::class,
            'json',
            [
                'object_to_populate' => $business,
                'groups' => ['business:write'],
            ]
        );

        $updatedbusiness = $this->businessService->updateBusiness($business);

        return $this->json($updatedbusiness, Response::HTTP_OK, [], [
            'groups' => ['business:read'],
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $business = $this->businessService->find($id);

        $this->businessService->delete($business);

        // 204 No Content is appropriate for successful deletes without body
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
