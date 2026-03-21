<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Mail;

use DR\Review\Doctrine\Type\CodeReviewType;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

#[CoversClass(MailCommentViewModelProvider::class)]
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
        $translator            = static::createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $this->provider = new MailCommentViewModelProvider($this->diffService, $this->revisionService, $this->diffFinder, $translator);
    }

    /**
     * @throws Throwable
     */
    public function testCreateCommentViewModelCommentCreated(): void
    {
        $reference = new LineReference(null, 'reference', 1, 2, 3);
        $comment   = (new Comment())->setUser((new User())->setName('name'));
        $comment->setFilePath('reference');
        $comment->setLineReference($reference);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->setRepository($repository);
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects($this->once())
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
     * @throws Throwable
     */
    public function testCreateCommentViewModelCommentReplied(): void
    {
        $reply     = (new CommentReply())->setUser((new User())->setName('name'));
        $reference = new LineReference(null, 'reference', 1, 2, 3);
        $comment   = new Comment();
        $comment->setFilePath('reference');
        $comment->setLineReference($reference);
        $comment->getReplies()->add($reply);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->setRepository($repository);
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects($this->once())
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
     * @throws Throwable
     */
    public function testCreateCommentViewModelResolvedByUser(): void
    {
        $reply     = new CommentReply();
        $reference = new LineReference(null, 'reference', 1, 2, 3);
        $comment   = new Comment();
        $comment->setFilePath('reference');
        $comment->setLineReference($reference);
        $comment->getReplies()->add($reply);
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->setRepository($repository);
        $review->getComments()->add($comment);
        $user = (new User())->setName('name');
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects($this->once())
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

    /**
     * @throws Throwable
     */
    public function testCreateCommentViewModelBranchReview(): void
    {
        $reference  = new LineReference(null, 'reference', 1, 2, 3);
        $comment    = (new Comment())->setUser((new User())->setName('name'))->setFilePath('reference')->setLineReference($reference);
        $repository = new Repository();
        $review     = (new CodeReview())->setType(CodeReviewType::BRANCH)->setRepository($repository)->setReferenceId('feature-branch');
        $review->getComments()->add($comment);
        $file = new DiffFile();
        $line = new DiffLine(0, []);

        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->diffService->expects($this->once())
            ->method('getDiffForBranch')
            ->with($review, [], 'feature-branch')
            ->willReturn([$file]);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with([$file], 'reference')->willReturn($file);
        $this->diffFinder->expects($this->once())
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
}
