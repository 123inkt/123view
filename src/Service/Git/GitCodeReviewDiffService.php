<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;

class GitCodeReviewDiffService
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @throws RepositoryException|ParseException
     */
    public function getDiff(array $revisions): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        /** @var Revision $revision */
        $revision = array_shift($revisions);

        // create branch
        $this->checkoutService->checkoutRevision($revision);

        // cherry-pick revisions
        $this->cherryPickService->cherryPickRevisions($revisions);

        // get the diff
        $files = $this->diffService->getBundledDiffFromRevisions($revision->getRepository());

        // check master
        $this->checkoutService->checkout($revision->getRepository(), 'master');
    }
}
