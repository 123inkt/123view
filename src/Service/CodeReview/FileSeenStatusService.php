<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;

class FileSeenStatusService
{
    public function __construct(private readonly FileSeenStatusRepository $statusRepository, private readonly ?User $user)
    {
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

    public function getFileSeenStatus(CodeReview $review): FileSeenStatusCollection
    {
        if ($this->user === null) {
            return new FileSeenStatusCollection();
        }

        $files = $this->statusRepository->findBy(['review' => (int)$review->getId(), 'user' => (int)$this->user->getId()]);

        return new FileSeenStatusCollection($files);
    }
}
