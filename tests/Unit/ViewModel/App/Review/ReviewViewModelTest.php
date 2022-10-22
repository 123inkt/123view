<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;
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
        $tree     = new FileTreeViewModel($this->createMock(DirectoryTreeNode::class), new ArrayCollection());
        $diffFile = new FileDiffViewModel(null);
        $form     = $this->createMock(FormView::class);
        $links    = [new ExternalLink()];

        $model = new ReviewViewModel($review, $tree, $diffFile, $form, $links);
        static::assertSame($links, $model->getExternalLinks());
        static::assertSame($tree, $model->getFileTreeModel());
        static::assertSame($review, $model->getReview());
        static::assertSame($form, $model->getAddReviewerForm());
        static::assertSame($diffFile, $model->getFileDiffViewModel());
    }

    /**
     * @covers ::getAuthors
     */
    public function testGetAuthors(): void
    {
        $tree = new FileTreeViewModel($this->createMock(DirectoryTreeNode::class), new ArrayCollection());

        $revision = new Revision();
        $revision->setAuthorEmail('holmes@example.com');
        $revision->setAuthorName('Sherlock Holmes');

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $model = new ReviewViewModel($review, $tree, new FileDiffViewModel(null), $this->createMock(FormView::class), []);

        static::assertSame(['holmes@example.com' => 'Sherlock Holmes'], $model->getAuthors());
    }

    /**
     * @covers ::isReviewer
     */
    public function testIsReviewer(): void
    {
        $userA = (new User())->setId(5);
        $userB = (new User())->setId(6);

        $tree = new FileTreeViewModel($this->createMock(DirectoryTreeNode::class), new ArrayCollection());

        $reviewer = new CodeReviewer();
        $reviewer->setUser($userA);

        $review = new CodeReview();
        $review->getReviewers()->add($reviewer);

        $model = new ReviewViewModel($review, $tree, new FileDiffViewModel(null), $this->createMock(FormView::class), []);

        static::assertNotNull($model->getReviewer($userA));
        static::assertNull($model->getReviewer($userB));
    }
}
