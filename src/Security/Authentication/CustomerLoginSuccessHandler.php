<?php

namespace App\Security\Authentication;

use App\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomerLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private bool $isDev;

    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private string $frontendBaseUrl,
        KernelInterface $kernel,
    ) {
        $this->frontendBaseUrl = rtrim($frontendBaseUrl, '/');
        $this->isDev = 'dev' === $kernel->getEnvironment();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        // Create refresh token
        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken(bin2hex(random_bytes(64)));
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setValid((new \DateTimeImmutable())->modify('+1 month'));

        $this->refreshTokenManager->save($refreshToken);

        // Get optional order token from query
        $orderToken = $request->query->get('order_token');

        // Redirect URL based on order flow or default dashboard
        $redirectPath = $orderToken ? '/order/confirm' : '/home';

        if ($this->isDev && 'application/json' === $request->headers->get('Content-Type')) {
            // Return JSON for Insomnia/Postman testing in dev
            return new JsonResponse([
                'token' => $jwt,
                'refresh_token' => $refreshToken->getRefreshToken(),
                'order_token' => $orderToken,
                'redirect_to' => $redirectPath,
            ]);
        }

        // Build redirect URL with tokens as query parameters
        $redirectUrl = $this->frontendBaseUrl.$redirectPath.'?'.http_build_query([
            'token' => $jwt,
            'refresh_token' => $refreshToken->getRefreshToken(),
            'order_token' => $orderToken,
        ]);

        return new RedirectResponse($redirectUrl);
    }
}
