<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabApi
{
    private ?Branches      $branches      = null;
    private ?MergeRequests $mergeRequests = null;

    public function __construct(private readonly HttpClientInterface $client, private readonly SerializerInterface $serializer)
    {
    }

    public function discussions(): Discussions
    {
        return $this->discussions ??= new Discussions($this->client);
    }

    public function branches(): Branches
    {
        return $this->branches ??= new Branches($this->client);
    }

    public function mergeRequests(): MergeRequests
    {
        return $this->mergeRequests ??= new MergeRequests($this->client, $this->serializer);
    }
}
