<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Utils\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class BranchRevisionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRevListService $revListService,
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionSorter $revisionSorter
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getRevisionsFor(Repository $repository, string $sourceBranch, string $targetBranch): array
    {
        // get all hashes in the branch
        $hashes = $this->revListService->getCommitsAheadOf($repository, $sourceBranch, $targetBranch);

        // get all revisions
        $revisions = $this->revisionRepository->findBy(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC']);

        // reindex array by revision id
        $revisions = Arrays::reindex($revisions, static fn(Revision $revision) => $revision->getId());

        // sort revisions by either sort uuid or create_timestamp
        return $this->revisionSorter->sort($revisions);
    }
}
