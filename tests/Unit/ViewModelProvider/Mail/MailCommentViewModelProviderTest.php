<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider\Mail;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider
 * @covers ::__construct
 */
class MailCommentViewModelProviderTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject $diffService;
    private DiffFinder&MockObject                 $diffFinder;
    private MailCommentViewModelProvider          $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(ReviewDiffServiceInterface::class);
        $this->diffFinder  = $this->createMock(DiffFinder::class);
        $this->provider    = new MailCommentViewModelProvider($this->diffService, $this->diffFinder);
    }

    /**
     * @covers ::createCommentViewModel
     * @covers ::getHeaderTitle
     * @covers ::getReplies
     * @throws Throwable
     */
    public function testCreateCommentViewModelCommentCreated(): void
    {
        $reference = new LineReference('reference', 1, 2, 3);
        $comment   = new Comment();
        $comment->setLineReference($reference);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 4)
            ->willReturn(['before' => [$line], 'after' => []]);

        $viewModel = $this->provider->createCommentViewModel($review, $comment);
        static::assertSame('mail.new.comment.by.user.on', $viewModel->headingTitle);
        static::assertSame($review, $viewModel->review);
        static::assertSame($comment, $viewModel->comment);
        static::assertSame([], $viewModel->replies);
        static::assertSame($file, $viewModel->file);
        static::assertSame([$line], $viewModel->linesBefore);
        static::assertSame([], $viewModel->linesAfter);
        static::assertNull($viewModel->resolvedBy);
    }

    /**
     * @covers ::createCommentViewModel
     * @covers ::getHeaderTitle
     * @covers ::getReplies
     * @throws Throwable
     */
    public function testCreateCommentViewModelCommentReplied(): void
    {
        $reply     = new CommentReply();
        $reference = new LineReference('reference', 1, 2, 3);
        $comment   = new Comment();
        $comment->setLineReference($reference);
        $comment->getReplies()->add($reply);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 4)
            ->willReturn(['before' => [$line], 'after' => []]);

        $viewModel = $this->provider->createCommentViewModel($review, $comment, $reply);
        static::assertSame('mail.new.reply.by.user.on', $viewModel->headingTitle);
        static::assertSame($review, $viewModel->review);
        static::assertSame($comment, $viewModel->comment);
        static::assertSame([$reply], $viewModel->replies);
        static::assertSame($file, $viewModel->file);
        static::assertSame([$line], $viewModel->linesBefore);
        static::assertSame([], $viewModel->linesAfter);
        static::assertNull($viewModel->resolvedBy);
    }

    /**
     * @covers ::createCommentViewModel
     * @covers ::getHeaderTitle
     * @covers ::getReplies
     * @throws Throwable
     */
    public function testCreateCommentViewModelResolvedByUser(): void
    {
        $reply     = new CommentReply();
        $reference = new LineReference('reference', 1, 2, 3);
        $comment   = new Comment();
        $comment->setLineReference($reference);
        $comment->getReplies()->add($reply);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);
        $review->getComments()->add($comment);
        $user = new User();
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 4)
            ->willReturn(['before' => [$line], 'after' => []]);

        $viewModel = $this->provider->createCommentViewModel($review, $comment, $reply, $user);
        static::assertSame('mail.comment.was.resolved.on', $viewModel->headingTitle);
        static::assertSame($review, $viewModel->review);
        static::assertSame($comment, $viewModel->comment);
        static::assertSame([$reply], $viewModel->replies);
        static::assertSame($file, $viewModel->file);
        static::assertSame([$line], $viewModel->linesBefore);
        static::assertSame([], $viewModel->linesAfter);
        static::assertSame($user, $viewModel->resolvedBy);
    }
}
