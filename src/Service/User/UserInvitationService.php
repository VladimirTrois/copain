<?php

namespace App\Service\User;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserInvitationService
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $frontendBaseUrl,
    ) {
    }

    public function sendPasswordSetUpInvitation(User $user): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $passwordSetupUrl = rtrim($this->frontendBaseUrl, '/') . '/password/setup?token=' . urlencode(
            $resetToken->getToken()
        );

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('vladimir.trois@gmail.com', 'Copain'))
            ->to($user->getEmail())
            ->subject('Complete your account setup')
            ->htmlTemplate('invitation_email.html.twig')
            ->context([
                'setupUrl' => $passwordSetupUrl,
                'user' => $user,
                'expiresAt' => $resetToken->getExpiresAt(),
            ]);

        $this->mailer->send($emailMessage);
        $this->logger->info(sprintf('Password setup invitation sent to %s', $user->getEmail()));
    }
}
