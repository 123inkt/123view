<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActionFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewActionFactory
 * @covers ::__construct
 */
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

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestAbsentAction(): void
    {
        static::assertNull($this->factory->createFromRequest(new Request()));
    }

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestAddCommentAction(): void
    {
        $request = new Request(['action' => 'add-comment:5:6:7', 'filePath' => '/foo/bar/text.txt']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(AddCommentAction::class, $action);

        static::assertSame('/foo/bar/text.txt', $action->lineReference->filePath);
        static::assertSame(5, $action->lineReference->line);
        static::assertSame(6, $action->lineReference->offset);
        static::assertSame(7, $action->lineReference->lineAfter);
    }

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestAddCommentReplyAction(): void
    {
        $comment = new Comment();
        $this->commentRepository->expects(self::once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'add-reply:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(AddCommentReplyAction::class, $action);
        static::assertSame($comment, $action->comment);
    }

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestEditCommentAction(): void
    {
        $comment = new Comment();
        $this->commentRepository->expects(self::once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'edit-comment:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(EditCommentAction::class, $action);
        static::assertSame($comment, $action->comment);
    }

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestEditCommentReplyAction(): void
    {
        $comment = new CommentReply();
        $this->replyRepository->expects(self::once())->method('find')->with(8)->willReturn($comment);

        $request = new Request(['action' => 'edit-reply:8']);
        $action  = $this->factory->createFromRequest($request);
        static::assertInstanceOf(EditCommentReplyAction::class, $action);
        static::assertSame($comment, $action->reply);
    }

    /**
     * @covers ::createFromRequest
     */
    public function testCreateFromRequestUnknownAction(): void
    {
        $request = new Request(['action' => 'foobar:8']);
        static::assertNull($this->factory->createFromRequest($request));
    }
}
