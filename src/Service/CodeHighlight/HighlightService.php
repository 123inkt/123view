<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class HighlightService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly HttpClientInterface $highlightjsClient)
    {
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function highlight(string $languageName, string $content): ?string
    {
        try {
            $response = $this->highlightjsClient->request('POST', '', ['query' => ['language' => $languageName], 'body' => $content]);
        } catch (Throwable $exception) {
            $this->logger?->info('Failed to get code highlighting: ' . $exception->getMessage());

            return null;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger?->info('Failed to get code highlighting: ' . $response->getContent(false));

            return null;
        }

        return $response->getContent(false);
    }
}
