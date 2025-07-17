<?php

namespace App\Controller\Customer;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Dto\Customer\Order\Update\OrderUpdateInput;
use App\Service\EntityValidator;
use App\Service\Order\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/customers/orders', name: 'api_customers_orders_')]
#[IsGranted('ROLE_CUSTOMER')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private SerializerInterface $serializer,
        private EntityValidator $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserInterface $user): JsonResponse
    {
        $orders = $this->orderService->listOrdersByCustomer($user);

        return $this->json($orders, Response::HTTP_OK, []);
    }

    #[Route('/{orderId}', name: 'show', methods: ['GET'])]
    public function show(int $orderId, UserInterface $user): JsonResponse
    {
        $order = $this->orderService->findOrderForCustomer($orderId, $user);
        $orderDTO = $this->orderService->mapOrderToShowDto($order);

        return $this->json($orderDTO, Response::HTTP_OK, []);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserInterface $user): JsonResponse
    {
        $json = $request->getContent();

        $input = $this->serializer->deserialize($json, OrderCreateInput::class, 'json');
        $this->validator->validate($input);

        $order = $this->orderService->createOrderForCustomer($input, $user);
        $orderDTO = $this->orderService->mapOrderToShowDto($order);

        return $this->json($orderDTO, Response::HTTP_CREATED, []);
    }

    #[Route('/{orderId}', name: 'api_orders_update', methods: ['PUT', 'PATCH'])]
    public function update(int $orderId, Request $request, UserInterface $user): JsonResponse
    {
        $order = $this->orderService->findOrderForCustomer($orderId, $user);

        $json = $request->getContent();

        $orderInput = $this->serializer->deserialize($json, OrderUpdateInput::class, 'json');
        $this->validator->validate($orderInput);

        $orderDTO = $this->orderService->updateOrderForCustomer($order, $orderInput, $user);

        return $this->json($orderDTO, Response::HTTP_CREATED, []);
    }
}
