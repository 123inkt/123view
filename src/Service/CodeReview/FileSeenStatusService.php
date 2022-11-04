<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;

class FileSeenStatusService
{
    public function __construct(private readonly FileSeenStatusRepository $statusRepository, private readonly ?User $user)
    {
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
