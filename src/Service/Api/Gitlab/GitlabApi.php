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
    private ?Users         $users         = null;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $gitlabClient,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function users(): Users
    {
        return $this->users ??= new Users($this->logger, $this->gitlabClient, $this->serializer);
    }

    public function discussions(): Discussions
    {
        return $this->discussions ??= new Discussions($this->gitlabClient);
    }

    public function branches(): Branches
    {
        return $this->branches ??= new Branches($this->logger, $this->gitlabClient);
    }

    public function mergeRequests(): MergeRequests
    {
        return $this->mergeRequests ??= new MergeRequests($this->gitlabClient, $this->serializer);
    }
}
