<?php

namespace App\Controller\Customer;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class AuthController extends AbstractController
{
    private bool $isDev;

    public function __construct(
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $entityManager,
        KernelInterface $kernel,
    ) {
        $this->isDev = 'dev' === $kernel->getEnvironment();
    }

    #[Route('/api/customers/login', name: 'customer_send_magic_link', methods: ['POST'])]
    public function customerRequestLogin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            throw new BadRequestHttpException('Email is required.');
        }

        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['email' => $email]);

        if (!$customer) {
            return new JsonResponse(['message' => 'If this email is registered, a login link has been sent.']);
        }

        // Generate one-time login link
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($customer);
        $url = $loginLinkDetails->getUrl();

        // Remove email, keep other keys as extra query parameters
        $extraParams = $data;
        unset($extraParams['email']);

        if (!empty($extraParams)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator.http_build_query($extraParams);
        }

        $lifetimeInSeconds = 600;

        $emailMessage = (new TemplatedEmail())
            ->from('no-reply@yourapp.com')
            ->to($customer->getEmail())
            ->subject('Your Login Link')
            ->htmlTemplate('customer_magic_link.html.twig')
            ->context([
                'login_link_url' => $url,
                'expires_in_minutes' => ceil($lifetimeInSeconds / 60),
            ]);

        $this->mailer->send($emailMessage);

        if ($this->isDev) {
            return new JsonResponse(['message' => 'If this email is registered, a login link has been sent.', 'url' => $url]);
        }

        return new JsonResponse(['message' => 'If this email is registered, a login link has been sent.']);
    }
}
