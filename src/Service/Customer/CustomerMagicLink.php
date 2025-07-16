<?php

namespace App\Service\Customer;

use App\Entity\Customer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class CustomerMagicLink
{
    public function __construct(
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendMagicLink(Customer $customer, array $extraParams = []): ?string
    {
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($customer);
        $url = $loginLinkDetails->getUrl();

        // Append extra parameters
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

        return $url;
    }
}
