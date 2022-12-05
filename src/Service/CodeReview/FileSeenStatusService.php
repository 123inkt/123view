<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;
use DR\GitCommitNotification\Service\Git\DiffTree\LockableGitDiffTreeService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class FileSeenStatusService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly LockableGitDiffTreeService $treeService,
        private readonly FileSeenStatusRepository $statusRepository,
        private readonly ?User $user
    ) {
    }

    public function markAsSeen(CodeReview $review, User $user, DiffFile|string|null $file): void
    {
        if ($file === null) {
            return;
        }

        $filePath = $file instanceof DiffFile ? (string)$file->getFile()?->getPathname() : $file;

        $status = new FileSeenStatus();
        $status->setReview($review);
        $status->setUser($user);
        $status->setFilePath($filePath);
        $status->setCreateTimestamp(time());
        $this->statusRepository->save($status, true);
    }

    public function markAsUnseen(CodeReview $review, User $user, DiffFile|string|null $file): void
    {
        if ($file === null) {
            return;
        }

        $filePath   = $file instanceof DiffFile ? (string)$file->getFile()?->getPathname() : $file;
        $seenStatus = $this->statusRepository->findOneBy(['review' => $review->getId(), 'user' => $user->getId(), 'filePath' => $filePath]);
        if ($seenStatus === null) {
            return;
        }

        $this->statusRepository->remove($seenStatus, true);
    }

    public function markAllAsUnseen(CodeReview $review, Revision $revision): void
    {
        try {
            $filePaths   = $this->treeService->getFilesInRevision($revision);
            $statusFiles = $this->statusRepository->findBy(['review' => $review->getId(), 'filePath' => $filePaths]);

            $lastItem = end($statusFiles);
            foreach ($statusFiles as $statusFile) {
                $this->statusRepository->remove($statusFile, $statusFile === $lastItem);
            }
            // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
            // @codeCoverageIgnoreEnd
        }
    }

    public function getFileSeenStatus(CodeReview $review): FileSeenStatusCollection
    {
        $files = $this->statusRepository->findBy(['review' => (int)$review->getId(), 'user' => (int)$this->user?->getId()]);

        return new FileSeenStatusCollection($files);
    }
}
