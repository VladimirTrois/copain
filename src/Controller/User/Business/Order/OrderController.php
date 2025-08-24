<?php

namespace App\Controller\User\Business\Order;

use App\Dto\User\Business\Order\List\OrderCriteriaInput;
use App\Service\Business\BusinessAccess;
use App\Service\Order\OrderBusinessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/businesses/{businessId}/orders')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private BusinessAccess $businessAccess,
        private OrderBusinessService $orderBusinessService,
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
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
}
