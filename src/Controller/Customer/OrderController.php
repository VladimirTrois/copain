<?php

namespace App\Controller\Customer;

use App\Dto\Customer\Order\Create\OrderCreateInput;
use App\Entity\Order;
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
        $orderDTO = $this->orderService->findOrderForCustomer($orderId, $user);

        return $this->json($orderDTO, Response::HTTP_OK, []);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserInterface $user): JsonResponse
    {
        $json = $request->getContent();

        $input = $this->serializer->deserialize($json, OrderCreateInput::class, 'json');
        $this->validator->validate($input);

        $orderDTO = $this->orderService->createOrderForCustomer($input, $user);

        return $this->json($orderDTO, Response::HTTP_CREATED, []);
    }

    // #[Route('/{id}', name: 'api_orders_update', methods: ['PUT', 'PATCH'])]
    // public function update(Order $order, Request $request, EntityManagerInterface $em): JsonResponse
    // {
    //     $user = $this->getUser();

    //     if ($order->getCustomer() !== $user) {
    //         return $this->json(['error' => 'Access denied'], 403);
    //     }

    //     $data = json_decode($request->getContent(), true);
    //     // Update logic here (you can offload to a service or manually patch fields)

    //     $em->flush();

    //     return $this->json($order, 200, [], ['groups' => 'order:read']);
    // }
}
