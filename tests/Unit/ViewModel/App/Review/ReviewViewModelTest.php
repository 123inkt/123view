<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel
 * @covers ::__construct
 */
class ReviewViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getExternalLinks
     * @covers ::getFileTree
     * @covers ::getReview
     * @covers ::getAddReviewerForm
     * @covers ::getSelectedFile
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(ReviewViewModel::class);
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

        $model = new ReviewViewModel($review, [], new DiffFile(), $this->createMock(FormView::class), []);

        static::assertSame(['holmes@example.com' => 'Sherlock Holmes'], $model->getAuthors());
    }

    /**
     * @covers ::isReviewer
     */
    public function testIsReviewer(): void
    {
        $userA = (new User())->setId(5);
        $userB = (new User())->setId(6);

        $reviewer = new CodeReviewer();
        $reviewer->setUser($userA);

        $review = new CodeReview();
        $review->getReviewers()->add($reviewer);

        $model = new ReviewViewModel($review, [], new DiffFile(), $this->createMock(FormView::class), []);

        static::assertTrue($model->isReviewer($userA));
        static::assertFalse($model->isReviewer($userB));
    }
}
