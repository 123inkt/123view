<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;

class FileSeenService
{
    public function __construct(private readonly FileSeenStatusRepository $seenStatusRepository)
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
        $this->seenStatusRepository->save($status, true);
    }

    public function markAsUnseen(CodeReview $review, User $user, string $filePath): void
    {
        $seenStatus = $this->seenStatusRepository->findOneBy(['review' => $review->getId(), 'user' => $user->getId(), 'filePath' => $filePath]);
        if ($seenStatus === null) {
            return;
        }

        $this->seenStatusRepository->remove($seenStatus, true);
    }
}
