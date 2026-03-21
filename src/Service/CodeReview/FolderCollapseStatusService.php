<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Service\User\UserEntityProvider;

class FolderCollapseStatusService
{
    public function __construct(private readonly FolderCollapseStatusRepository $statusRepository, private readonly UserEntityProvider $userProvider)
    {
    }

    public function getFolderCollapseStatus(CodeReview $review): FolderCollapseStatusCollection
    {
        $files = $this->statusRepository->findBy(['review' => $review->getId(), 'user' => (int)$this->userProvider->getUser()?->getId()]);

        return new FolderCollapseStatusCollection($files);
    }
}
