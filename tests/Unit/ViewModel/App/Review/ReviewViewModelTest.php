<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel
 * @covers ::__construct
 */
class ReviewViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getReview
     */
    public function testAccessorPairs(): void
    {
        $review   = new CodeReview();
        $diffFile = new FileDiffViewModel(null);

        $model = new ReviewViewModel($review, $diffFile);
        static::assertSame($review, $model->review);
        static::assertSame($diffFile, $model->fileDiffViewModel);
    }

    /**
     * @covers ::getAuthors
     */
    public function testGetAuthors(): void
    {
        $revision = new Revision();
        $revision->setAuthorEmail('holmes@example.com');
        $revision->setAuthorName('Sherlock Holmes');

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $model = new ReviewViewModel($review, new FileDiffViewModel(null));

        static::assertSame(['holmes@example.com' => 'Sherlock Holmes'], $model->getAuthors());
    }

    /**
     * @covers ::getReviewer
     */
    public function testIsReviewer(): void
    {
        $userA = (new User())->setId(5);
        $userB = (new User())->setId(6);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($userA);

        $review = new CodeReview();
        $review->getReviewers()->add($reviewer);

        $model = new ReviewViewModel($review, new FileDiffViewModel(null));

        static::assertNotNull($model->getReviewer($userA));
        static::assertNull($model->getReviewer($userB));
    }
}
