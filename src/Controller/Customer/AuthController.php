<?php

namespace App\Controller\Customer;

use App\Service\Customer\CustomerMagicLink;
use App\Service\Customer\CustomerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private bool $isDev;

    public function __construct(
        KernelInterface $kernel,
        private CustomerMagicLink $customerMagicLink,
        private CustomerService $customerService,
    ) {
        $this->isDev = $kernel->getEnvironment() === 'dev';
    }

    #[Route('/api/customers/login', name: 'customer_send_magic_link', methods: ['POST'])]
    public function customerRequestLogin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data)) {
            return $this->json([
                'error' => 'Invalid JSON payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'] ?? null;
        if (! $email) {
            throw new BadRequestHttpException('Email is required.');
        }

        $customer = $this->customerService->findOneBy([
            'email' => $email,
        ]);
        if (! $customer) {
            return new JsonResponse([
                'message' => 'If this email is registered, a login link has been sent.',
            ]);
        }

        $url = $this->customerMagicLink->sendMagicLink($customer, $data);

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
