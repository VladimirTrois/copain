<?php

namespace App\Controller\User\Business\Order;

use App\Service\Business\BusinessAccess;
use App\Service\Order\OrderBusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/businesses/{businessId}/orders')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private BusinessAccess $businessAccess,
        private OrderBusinessService $orderBusinessService,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', name: 'business_order_list', methods: ['GET'])]
    public function list(int $businessId): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $business = $this->businessAccess->getBusinessIfUserBelongs($businessId, $user);
        $orders = $this->orderBusinessService->listOrdersForBusiness($business);

        return $this->json($orders, Response::HTTP_OK);
    }
}
