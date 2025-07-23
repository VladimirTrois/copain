<?php

namespace App\Controller\Customer;

use App\Dto\Customer\Login\LoginInput;
use App\Service\Customer\CustomerMagicLink;
use App\Service\Customer\CustomerService;
use App\Service\EntityValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{
    private bool $isDev;

    public function __construct(
        KernelInterface $kernel,
        private CustomerMagicLink $customerMagicLink,
        private CustomerService $customerService,
        private SerializerInterface $serializer,
        private EntityValidator $validator
    ) {
        $this->isDev = $kernel->getEnvironment() === 'dev';
    }

    #[Route('/api/customers/login', name: 'customer_send_magic_link', methods: ['POST'])]
    public function customerRequestLogin(Request $request): JsonResponse
    {
        $loginInput = $this->serializer->deserialize($request->getContent(), LoginInput::class, 'json');
        $this->validator->validate($loginInput);

        $customer = $this->customerService->findOneBy([
            'email' => $loginInput->email,
        ]);
        if (! $customer) {
            return new JsonResponse([
                'message' => 'If this email is registered, a login link has been sent.',
            ]);
        }

        $url = $this->customerMagicLink->sendMagicLink($customer);

        if ($this->isDev) {
            return new JsonResponse([
                'message' => 'If this email is registered, a login link has been sent.',
                'url' => $url,
            ]);
        }

        return new JsonResponse([
            'message' => 'If this email is registered, a login link has been sent.',
        ]);
    }
}
