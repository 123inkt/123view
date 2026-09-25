<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FileSeenStatusRepository;
use DR\Review\Service\Git\DiffTree\LockableGitDiffTreeService;
use DR\Review\Service\User\UserEntityProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class FileSeenStatusService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly LockableGitDiffTreeService $treeService,
        private readonly FileSeenStatusRepository $statusRepository,
        private readonly UserEntityProvider $userProvider
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
        $files = $this->statusRepository->findBy(['review' => $review->getId(), 'user' => $this->userProvider->getCurrentUser()->getId()]);

        return new FileSeenStatusCollection($files);
    }
}
