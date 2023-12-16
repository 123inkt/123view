<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabApi
{
    private ?Branches      $branches      = null;
    private ?MergeRequests $mergeRequests = null;
    private ?Discussions   $discussions   = null;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function discussions(): Discussions
    {
        return $this->discussions ??= new Discussions($this->client);
    }

    public function branches(): Branches
    {
        return $this->branches ??= new Branches($this->logger, $this->client);
    }

    public function mergeRequests(): MergeRequests
    {
        return $this->mergeRequests ??= new MergeRequests($this->client, $this->serializer);
    }
}
