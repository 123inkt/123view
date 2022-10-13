<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;

class ReviewViewModel
{
    public function __construct(private readonly CodeReview $review)
    {
    }

    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->review->getRevisions() as $revision) {
            $authors[$revision->getAuthorEmail()] = $revision->getAuthorName();
        }

        return $authors;
    }

    public function getReview(): CodeReview
    {
        return $this->review;
    }
}
