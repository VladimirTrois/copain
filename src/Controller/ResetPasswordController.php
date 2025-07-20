<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/api/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/request', name: 'api_reset_password_request', methods: ['POST'])]
    public function requestResetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data)) {
            return $this->json([
                'error' => 'Invalid JSON payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'] ?? null;

        if (! is_string($email)) {
            return $this->json([
                'error' => 'Invalid payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if (! $user) {
            return $this->json([
                'message' => 'If the email exists, a reset link will be sent.',
            ]);
        }

        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $address = new Address('vladimir.trois@gmail.com', 'Copain');

        $emailMessage = (new TemplatedEmail())
            ->from($address)
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->htmlTemplate('reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($emailMessage);
        $this->logger->info('Email sent successfully');

        return $this->json([
            'message' => 'Reset email sent if address is valid.',
        ]);
    }

    #[Route('/reset', name: 'api_reset_password_reset', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data)) {
            return $this->json([
                'error' => 'Invalid JSON payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (! is_string($token) || ! is_string($newPassword)) {
            return $this->json([
                'error' => 'Invalid payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json([
                'error' => $e->getReason(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));

        $this->resetPasswordHelper->removeResetRequest($token);

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
