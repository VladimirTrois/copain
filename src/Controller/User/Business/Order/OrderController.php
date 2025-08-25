<?php

namespace App\Controller\User\Business\Order;

use App\Dto\Shared\Order\OrderUpdateInput;
use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Service\Business\BusinessAccess;
use App\Service\EntityValidator;
use App\Service\Order\OrderBusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/businesses/{businessId}/orders')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private BusinessAccess $businessAccess,
        private OrderBusinessService $orderBusinessService,
        private DenormalizerInterface $denormalizer,
        private SerializerInterface $serializer,
        private EntityValidator $validator,
    ) {
    }

    #[Route('', name: 'business_order_list', methods: ['GET'])]
    public function list(int $businessId, Request $request): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        /** @var OrderCriteriaInput $criteria */
        $criteria = $this->denormalizer->denormalize($request->query->all(), OrderCriteriaInput::class);

        $this->validator->validate($criteria);

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $orders = $this->orderBusinessService->listOrdersForBusiness($business, $criteria);

        return $this->json($orders, Response::HTTP_OK);
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
    public function update(int $businessId, int $orderId, Request $request): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $order = $this->orderBusinessService->findOrderForBusiness($orderId, $business);

        $json = $request->getContent();

        $orderInput = $this->serializer->deserialize($json, OrderUpdateInput::class, 'json');
        $this->validator->validate($orderInput);

        $orderDto = $this->orderBusinessService->updateOrderForBusiness($order, $orderInput, $business);

        return $this->json($orderDto, Response::HTTP_CREATED, []);
    }
}
