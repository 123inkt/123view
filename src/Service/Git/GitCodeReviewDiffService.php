<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;

class GitCodeReviewDiffService
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitResetService $resetService
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getDiffFiles(array $revisions): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        /** @var Revision $revision */
        $revision = array_shift($revisions);
        /** @var Repository $repository */
        $repository = $revision->getRepository();

        // create branch
        $this->checkoutService->checkoutRevision($revision);

        // cherry-pick revisions
        $this->cherryPickService->cherryPickRevisions($revisions);

        // get the diff
        $files = $this->diffService->getBundledDiffFromRevisions($repository);

        // reset the repository again
        $this->resetService->resetHard($repository);

        // check master
        $this->checkoutService->checkout($repository, 'master');

        return $files;
    }
}
