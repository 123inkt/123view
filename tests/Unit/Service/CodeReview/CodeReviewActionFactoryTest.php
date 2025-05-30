<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Model\Review\Action\AddCommentAction;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CodeReviewActionFactory::class)]
class CodeReviewActionFactoryTest extends AbstractTestCase
{
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $replyRepository;
    private CodeReviewActionFactory           $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->replyRepository   = $this->createMock(CommentReplyRepository::class);
        $this->factory           = new CodeReviewActionFactory($this->commentRepository, $this->replyRepository);
    }

    public function testCreateFromRequestAbsentAction(): void
    {
        static::assertNull($this->factory->createFromRequest(new Request()));
    }

    public function testCreateFromRequestAddCommentAction(): void
    {
        $request = new Request(['action' => 'add-comment:5:6:7', 'filePath' => '/foo/bar/text.txt']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(AddCommentAction::class, $action);

        static::assertSame('/foo/bar/text.txt', $action->lineReference->newPath);
        static::assertSame(5, $action->lineReference->line);
        static::assertSame(6, $action->lineReference->offset);
        static::assertSame(7, $action->lineReference->lineAfter);
    }

    public function testCreateFromRequestAddCommentReplyAction(): void
    {
        $comment = new Comment();
        $this->commentRepository->expects($this->once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'add-reply:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(AddCommentReplyAction::class, $action);
        static::assertSame($comment, $action->comment);
    }

    public function testCreateFromRequestEditCommentAction(): void
    {
        $comment = new Comment();
        $this->commentRepository->expects($this->once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'edit-comment:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(EditCommentAction::class, $action);
        static::assertSame($comment, $action->comment);
    }

    public function testCreateFromRequestEditCommentReplyAction(): void
    {
        $comment = new CommentReply();
        $this->replyRepository->expects($this->once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'edit-reply:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(EditCommentReplyAction::class, $action);
        static::assertSame($comment, $action->reply);
    }

    public function testCreateFromRequestUnknownAction(): void
    {
        $request = new Request(['action' => 'foobar:8']);
        static::assertNull($this->factory->createFromRequest($request));
    }
}
