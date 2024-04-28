<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;

class FolderCollapseService
{
    public function __construct(private readonly FolderCollapseStatusRepository $statusRepository, private readonly ?User $user)
    {
    }

    public function getFolderCollapseStatus(CodeReview $review): FolderCollapseStatusCollection
    {
        $files = $this->statusRepository->findBy(['review' => (int)$review->getId(), 'user' => (int)$this->user?->getId()]);

        return new FolderCollapseStatusCollection($files);
    }
}
