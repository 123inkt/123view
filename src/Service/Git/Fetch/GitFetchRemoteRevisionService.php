<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use Carbon\Carbon;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Fetch\BranchUpdate;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Review\UniqueCommitIterator;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Utility\Arrays;
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
     * @return iterable<Commit>
     * @throws Exception
     */
    public function fetchRevisionFromRemote(Repository $repository, int $maxRevisions): iterable
    {
        // fetch new revisions from remote
        $changes = $this->fetchService->fetch($repository);
        $this->logger?->info(
            "GitFetchRemoteRevisionService: fetched {count} updated or new branches from {name}",
            ['count' => count($changes), 'name' => $repository->getName()]
        );

        // find the last revision and get the commits since then - 3 hours
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);
        $since          = $latestRevision === null ? null : Carbon::createFromTimestampUTC((int)$latestRevision->getCreateTimestamp() - (3 * 3600));

        return new UniqueCommitIterator(
            [
                function (bool &$repeat) use ($repository, &$since, $maxRevisions): array { // @codingStandardsIgnoreLine
                    $this->logger?->info('Fetch new commits since: {date}', ['date' => $since?->format('c')]);
                    $commits = $this->logService->getCommitsSince($repository, $since, $maxRevisions);
                    $since   = Arrays::lastOrNull($commits) ?? $since;
                    $repeat  = count($commits) >= $maxRevisions;

                    return $commits;
                },
                function () use ($repository, $changes) {
                    $commits = [];
                    foreach ($changes as $change) {
                        if ($change instanceof BranchUpdate) {
                            $this->logger?->info('Fetch new commits from branch: {branch}', ['branch' => $change->remoteBranch]);
                            $commits[] = $this->logService->getCommitsFromRange($repository, $change->fromHash, $change->toHash);
                        }
                    }

                    return array_merge(...$commits);
                }
            ]
        );
    }
}
