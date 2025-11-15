<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLocationService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class AnthropicPromptService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        #[Autowire(env: 'ANTHROPIC_HOST')] private string $host,
        #[Autowire(env: 'ANTHROPIC_PORT')] private int $port,
        private readonly HttpClientInterface $httpClient,
        private readonly GitRepositoryLocationService $locationService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function prompt(Repository $repository, string $message): ?string
    {
        $url = sprintf('http://%s:%d/query', $this->host, $this->port);

        $response = $this->httpClient->request(
            'POST',
            $url,
            [
                'json' => [
                    'review' => $message,
                    'projectIDr' => $this->locationService->getLocation($repository),
                ]
            ]
        );

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                'Anthropic prompt request failed',
                ['status_code' => $response->getStatusCode(), 'body' => $response->getContent(false)]
            );

            return null;
        }

        $message = $response->getContent();
        $message = preg_replace('/^.*?## /s', '## ',  $message);

        return str_replace(":**", "**", $message);
    }
}
