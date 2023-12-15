<?php
declare(strict_types=1);

namespace DR\Review\ExternalTool\Gitlab;

use DR\Review\Service\Api\Gitlab\GitlabApi;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class GitlabService
{
    public function __construct(private readonly GitlabApi $gitlabApi, private readonly CacheInterface $gitlabCache)
    {
    }

    /**
     * @throws Throwable
     */
    public function getBranchUrl(int $projectId, string $remoteRef): ?string
    {
        return $this->gitlabCache->get(
            sprintf("branch-url-%s-%s", $projectId, $remoteRef),
            fn() => $this->gitlabApi->branches()->getBranch($projectId, $remoteRef)['web_url'] ?? null
        );
    }

    /**
     * @throws Throwable
     */
    public function getMergeRequestUrl(int $projectId, string $remoteRef): ?string
    {
        return $this->gitlabCache->get(
            sprintf("merge-request-url-%s-%s", $projectId, $remoteRef),
            fn() => $this->gitlabApi->mergeRequests()->findByRemoteRef($projectId, $remoteRef)['web_url'] ?? null
        );
    }
}
