<?php

namespace App\Tests\Functional\Customer\Auth;

use App\Tests\BaseTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class CustomerBaseTestCase extends BaseTestCase
{
    public const FRONTEND_BASE_URL = 'https://test.com';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    protected function simulateMagicLinkClickAndGetRedirect(string $magicLinkUrl): string
    {
        $parsedUrl = parse_url($magicLinkUrl);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $magicLinkPath = $path . $query;

        $this->client->followRedirects(false);
        $this->client->request('GET', $magicLinkPath);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $url = $this->client->getResponse()
            ->headers->get('Location');

        $this->assertIsString($url);

        return $url;
    }

    /**
     * @return array<mixed>
     */
    protected function extractQueryParametersFromUrl(string $url): array
    {
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);

        return $queryParams;
    }

    protected function assertContainsMagicLink(TemplatedEmail $email): string
    {
        $htmlBody = $email->getHtmlBody();
        if (! is_string($htmlBody)) {
            throw new \RuntimeException('Email body is not a string');
        }

        preg_match('/https?:\/\/[^\s"]+/', $htmlBody, $matches);
        $this->assertNotEmpty($matches, 'No URL found in email body');

        return $matches[0] ?? throw new \RuntimeException('No URL match found');
    }
}
