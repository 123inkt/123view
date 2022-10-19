<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
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
        $review   = new CodeReview();
        /** @var DirectoryTreeNode<DiffFile> $tree */
        $tree     = new DirectoryTreeNode('root');
        $diffFile = new DiffFile();
        $form     = $this->createMock(FormView::class);
        $links    = [new ExternalLink()];

        $model = new ReviewViewModel($review, $tree, $diffFile, $form, $links);
        static::assertSame($links, $model->getExternalLinks());
        static::assertSame($tree, $model->getFileTree());
        static::assertSame($review, $model->getReview());
        static::assertSame($form, $model->getAddReviewerForm());
        static::assertSame($diffFile, $model->getSelectedFile());
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

        $model = new ReviewViewModel($review, new DirectoryTreeNode('root'), new DiffFile(), $this->createMock(FormView::class), []);

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

        $model = new ReviewViewModel($review, new DirectoryTreeNode('root'), new DiffFile(), $this->createMock(FormView::class), []);

        static::assertTrue($model->isReviewer($userA));
        static::assertFalse($model->isReviewer($userB));
    }
}
