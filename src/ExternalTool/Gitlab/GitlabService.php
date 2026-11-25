<?php
declare(strict_types=1);

namespace DR\Review\ExternalTool\Gitlab;

use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

class GitlabService
{
    public function __construct(private readonly GitlabApi $gitlabApi, #[Target('gitlabCache')] private readonly CacheInterface $gitlabCache)
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
        return $this->getMergeRequest($projectId, $remoteRef)['web_url'] ?? null;
    }

    /**
     * @throws Throwable
     */
    public function getMergeRequestTargetBranch(int $projectId, string $remoteRef): ?string
    {
        return $this->getMergeRequest($projectId, $remoteRef)['target_branch'] ?? null;
    }

    /**
     * @return array{id: int, iid: int, title: string, web_url: string, target_branch?: string}|null
     * @throws Throwable
     */
    public function getMergeRequest(int $projectId, string $remoteRef): ?array
    {
        return $this->gitlabCache->get(
            sprintf("merge-request-url-%s-%s", $projectId, $remoteRef),
            fn() => $this->gitlabApi->mergeRequests()->findByRemoteRef($projectId, $remoteRef)
        );
    }

    /**
     * @return Note[]
     * @throws Throwable
     */
    public function getMergeRequestNotes(int $projectId, int $mergeRequestIId): array
    {
        return $this->gitlabCache->get(
            sprintf("merge-request-notes-%s-%s", $projectId, $mergeRequestIId),
            function (ItemInterface $item) use ($projectId, $mergeRequestIId): array {
                $item->expiresAfter(60);

                return $this->gitlabApi->mergeRequests()->notes($projectId, $mergeRequestIId);
            }
        );
    }
}
