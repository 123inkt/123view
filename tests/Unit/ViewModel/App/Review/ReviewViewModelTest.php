<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\ReviewViewModel
 * @covers ::__construct
 */
class ReviewViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::isDescriptionVisible
     * @covers ::setDescriptionVisible
     * @covers ::setSidebarTabMode
     * @covers ::getSidebarTabMode
     * @covers ::setAddReviewerForm
     * @covers ::getAddReviewerForm
     * @covers ::getRevisionViewModel
     * @covers ::setRevisionViewModel
     * @covers ::setFileTreeModel
     * @covers ::getFileTreeModel
     * @covers ::getFileDiffViewModel
     * @covers ::setFileDiffViewModel
     * @covers ::getReviewSummaryViewModel
     * @covers ::setReviewSummaryViewModel
     * @covers ::getVisibleRevisionCount
     * @covers ::setVisibleRevisionCount
     * @covers ::getOpenComments
     * @covers ::getAuthors
     * @covers ::getReviewer
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(ReviewViewModel::class);
    }

    /**
     * @covers ::getOpenComments
     */
    public function testGetOpenComments(): void
    {
        $commentA = new Comment();
        $commentA->setState(CommentStateType::OPEN);
        $commentB = new Comment();
        $commentB->setState(CommentStateType::OPEN);
        $commentC = new Comment();
        $commentC->setState(CommentStateType::RESOLVED);
        $review = new CodeReview();
        $review->getComments()->add($commentA);
        $review->getComments()->add($commentB);
        $review->getComments()->add($commentC);

        $model = new ReviewViewModel($review);
        static::assertSame(2, $model->getOpenComments());
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

        $model = new ReviewViewModel($review);

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

        $model = new ReviewViewModel($review);

        static::assertNotNull($model->getReviewer($userA));
        static::assertNull($model->getReviewer($userB));
    }
}
