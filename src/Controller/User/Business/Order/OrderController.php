<?php

namespace App\Controller\User\Business\Order;

use App\Dto\Shared\Order\OrderUpdateInput;
use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Service\Business\BusinessAccess;
use App\Service\EntityValidator;
use App\Service\Order\OrderBusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/businesses/{businessId}/orders')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private BusinessAccess $businessAccess,
        private OrderBusinessService $orderBusinessService,
        private EntityValidator $validator,
    ) {
    }

    #[Route('', name: 'business_order_list', methods: ['GET'])]
    public function list(int $businessId, #[MapQueryString] OrderCriteriaInput $criteria): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $this->validator->validate($criteria);

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $paginatedResults = $this->orderBusinessService->listOrdersForBusiness($business, $criteria);

        return $this->json($paginatedResults, Response::HTTP_OK);
    }

    #[Route('/{orderId}', name: 'business_order_show', methods: ['GET'])]
    public function show(int $businessId, int $orderId): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $order = $this->orderBusinessService->findOrderForBusiness($orderId, $business);
        $orderDto = $this->orderBusinessService->mapOrderToShowDto($order);

        return $this->json($orderDto, Response::HTTP_OK);
    }

    #[Route('/{orderId}', name: 'business_order_update', methods: ['PATCH'])]
    public function update(
        int $businessId,
        int $orderId,
        #[MapRequestPayload]
        OrderUpdateInput $orderInput
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $order = $this->orderBusinessService->findOrderForBusiness($orderId, $business);

        $this->validator->validate($orderInput);

        $orderDto = $this->orderBusinessService->updateOrderForBusiness($order, $orderInput, $business);

        return $this->json($orderDto, Response::HTTP_CREATED, []);
    }
}
