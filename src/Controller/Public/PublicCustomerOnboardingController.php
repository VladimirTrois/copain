<?php

namespace App\Controller\Public;

use App\Dto\Customer\Register\CustomerCreateInput;
use App\Dto\Public\PublicOrderCreateInput;
use App\Service\Customer\CustomerMagicLink;
use App\Service\Customer\CustomerService;
use App\Service\EntityValidator;
use App\Service\Order\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

// PublicOrderController.php
#[Route('/api/customers', name: 'api_customers_')]
class PublicCustomerOnboardingController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private SerializerInterface $serializer,
        private EntityValidator $validator,
        private CustomerService $customerService,
        private CustomerMagicLink $customerMagicLink,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function customerRegister(Request $request): JsonResponse
    {
        $input = $this->serializer->deserialize($request->getContent(), CustomerCreateInput::class, 'json');
        $this->validator->validate($input);

        $customer = $this->customerService->findOneBy(['email' => $input->email]);
        if ($customer) {
            return $this->json(['message' => 'Please check your emails to confirm.'], Response::HTTP_ACCEPTED);
        }

        $customer = $this->customerService->createCustomer($input);
        $url = $this->customerMagicLink->sendMagicLink($customer);

        return $this->json(['message' => 'Please check your emails to confirm.', 'url' => $url], Response::HTTP_ACCEPTED);
    }

    #[Route('/public/orders', name: 'create', methods: ['POST'])]
    public function createPublicOrder(Request $request): JsonResponse
    {
        $input = $this->serializer->deserialize($request->getContent(), PublicOrderCreateInput::class, 'json');
        $this->validator->validate($input);

        $customer = $this->customerService->findOneBy(['email' => $input->customer->email]);
        if (!$customer) {
            $customer = $this->customerService->createCustomer($input->customer);
        }

        $order = $this->orderService->createOrderForCustomer($input->order, $customer);
        $orderDto = $this->orderService->mapOrderToShowDto($order);

        // Send magic link using $input->email and the generated token (attach order ID for confirmation)
        $url = $this->customerMagicLink->sendMagicLink($customer, ['order_token' => $orderDto->id]);

        return $this->json(['message' => 'Order received. Please check your emails to confirm.'], Response::HTTP_ACCEPTED);
    }
}
