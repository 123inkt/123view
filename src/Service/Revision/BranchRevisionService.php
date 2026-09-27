<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Utils\Arrays;
use Throwable;

readonly class BranchRevisionService
{
    public function __construct(
        private CacheableGitRevListService $revListService,
        private RevisionRepository $revisionRepository,
        private RevisionSorter $revisionSorter
    ) {
    }

    /**
     * @return Revision[]
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
