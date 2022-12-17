<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use Carbon\Carbon;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitFetchRemoteRevisionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private LockableGitLogService $logService,
        private LockableGitFetchService $fetchService,
        private RevisionRepository $revisionRepository,
    ) {
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function fetchRevisionFromRemote(Repository $repository, int $maxRevisions): array
    {
        // get commits from fetch
        $changes = $this->fetchService->fetch($repository);
        $this->logger?->info(
            "GitFetchRemoteRevisionService: fetched {count} updated or new branches from {name}",
            ['count' => count($changes), 'name' => $repository->getName()]
        );

        // get commit information for the changes
        $commitsGroup = [];
        foreach ($changes as $change) {
            if ($change instanceof BranchUpdate) {
                $commitsGroup[] = $this->logService->getCommitsFromRange($repository, $change->fromHash, $change->toHash);
            }
        }

        // find the last revision and get the commits since then +- 2 hours
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);
        $since          = $latestRevision === null ? null : Carbon::createFromTimestampUTC((int)$latestRevision->getCreateTimestamp() - 7200);
        $commitsGroup[] = $this->logService->getCommitsSince($repository, $since, $maxRevisions);

        // make unique
        $commits = [];
        foreach ($commitsGroup as $group) {
            foreach ($group as $commit) {
                $commits[$commit->commitHashes[0]] = $commit;
            }
        }

        return $commits;
    }
}
