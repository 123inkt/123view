<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Mail;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\Mail\MailCommentViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\Mail\MailCommentViewModelProvider
 * @covers ::__construct
 */
class MailCommentViewModelProviderTest extends AbstractTestCase
{
    private ReviewDiffServiceInterface&MockObject $diffService;
    private CodeReviewRevisionService&MockObject  $revisionService;
    private DiffFinder&MockObject                 $diffFinder;
    private MailCommentViewModelProvider          $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->diffFinder      = $this->createMock(DiffFinder::class);
        $translator            = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $this->provider = new MailCommentViewModelProvider($this->diffService, $this->revisionService, $this->diffFinder, $translator);
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
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 6)
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
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 6)
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
        $review->getComments()->add($comment);
        $user = new User();
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects(self::once())
            ->method('findLinesAround')
            ->with($file, $reference, 6)
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
