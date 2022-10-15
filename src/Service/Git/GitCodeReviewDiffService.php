<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class GitCodeReviewDiffService
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
        private readonly CacheInterface $revisionCache,
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws Throwable
     */
    public function getDiffFiles(array $revisions): array
    {
        if (count($revisions) === 0) {
            return [];
        }

        // gather hashes
        $hashes = array_map(static fn(Revision $revision) => $revision->getCommitHash(), $revisions);

        /** @var Revision $revision */
        $revision = array_shift($revisions);
        /** @var Repository $repository */
        $repository = $revision->getRepository();

        $key = sprintf('%s-%s', $repository->getId(), implode('-', $hashes));

        return $this->revisionCache->get($key, fn() => $this->getDiffFilesFromGit($repository, $revision, $revisions));
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    private function getDiffFilesFromGit(Repository $repository, Revision $revision, array $revisions): array
    {
        if (count($revisions) === 0) {
            // get the diff for the single revision
            return $this->diffService->getDiffFromRevision($revision);
        }

        // create branch
        $branchName = $this->checkoutService->checkoutRevision($revision);

        try {
            // cherry-pick revisions
            $this->cherryPickService->cherryPickRevisions($revisions);

            // get the diff
            $files = $this->diffService->getBundledDiffFromRevisions($repository);
        } catch (RepositoryException|ProcessFailedException $exception) {
            $this->cherryPickService->cherryPickAbort($repository);

            throw $exception;
        } finally {
            // reset the repository again
            $this->resetService->resetHard($repository);

            // checkout master
            $this->checkoutService->checkout($repository, 'master');

            // cleanup branch
            $this->branchService->deleteBranch($repository, $branchName);
        }

        return $files;
    }
}
