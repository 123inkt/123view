<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Form\Review\EditCommentFormType;
use DR\GitCommitNotification\Form\Review\EditCommentReplyFormType;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\CommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\CommentViewModelProvider
 * @covers ::__construct
 */
class CommentViewModelProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject    $commentRepository;
    private FormFactoryInterface&MockObject $formFactory;
    private DiffFinder&MockObject           $diffFinder;
    private CommentViewModelProvider        $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->formFactory       = $this->createMock(FormFactoryInterface::class);
        $this->diffFinder        = $this->createMock(DiffFinder::class);
        $this->provider          = new CommentViewModelProvider($this->commentRepository, $this->formFactory, $this->diffFinder);
    }

    /**
     * @covers ::getAddCommentViewModel
     */
    public function testGetAddCommentViewModel(): void
    {
        $reference = new LineReference();
        $file      = new DiffFile();
        $line      = new DiffLine(0, []);
        $review    = new CodeReview();
        $action    = new AddCommentAction($reference);

        $this->diffFinder->expects(self::once())->method('findLineInFile')->with($file, $reference)->willReturn($line);
        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $reference]);

        $viewModel = $this->provider->getAddCommentViewModel($review, $file, $action);
        static::assertSame($line, $viewModel->diffLine);
    }

    /**
     * @covers ::getEditCommentViewModel
     */
    public function testGetEditCommentViewModelNullCommentShouldReturnNull(): void
    {
        $action = new EditCommentAction(null);
        static::assertNull($this->provider->getEditCommentViewModel($action));
    }

    /**
     * @covers ::getEditCommentViewModel
     */
    public function testGetEditCommentViewModel(): void
    {
        $comment = new Comment();
        $action  = new EditCommentAction($comment);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(EditCommentFormType::class, $comment, ['comment' => $comment]);

        $viewModel = $this->provider->getEditCommentViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($comment, $viewModel->comment);
    }

    /**
     * @covers ::getReplyCommentViewModel
     */
    public function testGetReplyCommentViewModelNullCommentShouldReturnNull(): void
    {
        $action = new AddCommentReplyAction(null);
        static::assertNull($this->provider->getReplyCommentViewModel($action));
    }

    /**
     * @covers ::getReplyCommentViewModel
     */
    public function testGetReplyCommentViewModel(): void
    {
        $comment = new Comment();
        $action  = new AddCommentReplyAction($comment);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(AddCommentReplyFormType::class, null, ['comment' => $comment]);

        $viewModel = $this->provider->getReplyCommentViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($comment, $viewModel->comment);
    }

    /**
     * @covers ::getEditCommentReplyViewModel
     */
    public function testGetEditCommentReplyViewModelNullCommentShouldReturnNull(): void
    {
        $action = new EditCommentReplyAction(null);
        static::assertNull($this->provider->getEditCommentReplyViewModel($action));
    }

    /**
     * @covers ::getEditCommentReplyViewModel
     */
    public function testGetEditCommentReplyViewModel(): void
    {
        $reply  = new CommentReply();
        $action = new EditCommentReplyAction($reply);

        $this->formFactory->expects(self::once())
            ->method('create')
            ->with(EditCommentReplyFormType::class, $reply, ['reply' => $reply]);

        $viewModel = $this->provider->getEditCommentReplyViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($reply, $viewModel->reply);
    }

    /**
     * @covers ::getCommentsViewModel
     */
    public function testGetCommentsViewModel(): void
    {
        $commentA = new Comment();
        $commentA->setLineReference(new LineReference('comment-1', 1, 2, 3));
        $commentB = new Comment();
        $commentB->setLineReference(new LineReference('comment-2', 4, 5, 6));
        $comments = [$commentA, $commentB];
        $review   = new CodeReview();
        $file     = new DiffFile();
        $line     = new DiffLine(0, []);

        $file->filePathAfter = '/path/to/file';

        $this->commentRepository->expects(self::once())->method('findByReview')->with($review, '/path/to/file')->willReturn($comments);
        $this->diffFinder->expects(self::exactly(2))->method('findLineInFile')
            ->withConsecutive([$file, $commentA->getLineReference()], [$file, $commentB->getLineReference()])
            ->willReturn($line, null);

        $viewModel = $this->provider->getCommentsViewModel($review, $file);
        static::assertSame([$commentA], $viewModel->getComments($line));
        static::assertSame([$commentB], $viewModel->getDetachedComments());
    }
}
