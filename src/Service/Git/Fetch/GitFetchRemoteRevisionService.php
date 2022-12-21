<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Fetch\BranchCreation;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitFetchRemoteRevisionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private LockableGitLogService $logService, private LockableGitFetchService $fetchService)
    {
    }

    /**
     * @return iterable<Commit>
     * @throws Exception
     */
    public function fetchRevisionFromRemote(Repository $repository): iterable
    {
        // fetch new revisions from remote
        $changes = $this->fetchService->fetch($repository);
        $this->logger?->info(
            "GitFetchRemoteRevisionService: fetched {count} updated or new branches from {name}",
            ['count' => count($changes), 'name' => $repository->getName()]
        );

        $commits = [];
        foreach ($changes as $change) {
            if ($change instanceof BranchCreation) {
                $this->logger?->info('Fetch commits from new branch: {branch}', ['branch' => $change->remoteBranch]);
                $commits[] = $this->logService->getCommitsFromRange($repository, 'origin/master', $change->remoteBranch);
            } elseif ($change instanceof BranchUpdate) {
                $this->logger?->info('Fetch new commits from existing branch: {branch}', ['branch' => $change->remoteBranch]);
                $commits[] = $this->logService->getCommitsFromRange($repository, $change->fromHash, $change->toHash);
            }
        }

        return array_merge(...$commits);
    }
}
