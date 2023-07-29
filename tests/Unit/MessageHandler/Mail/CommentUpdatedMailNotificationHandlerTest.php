<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\Mail\CommentUpdatedMailNotificationHandler
 * @covers ::__construct
 */
class CommentUpdatedMailNotificationHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject         $mailService;
    private CommentRepository&MockObject          $commentRepository;
    private CommentMentionService&MockObject      $mentionService;
    private CommentUpdatedMailNotificationHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailService       = $this->createMock(CommentMailService::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->mentionService    = $this->createMock(CommentMentionService::class);
        $this->handler           = new CommentUpdatedMailNotificationHandler($this->mailService, $this->commentRepository, $this->mentionService);
    }

    /**
     * @covers ::accepts
     */
    public function testAccepts(): void
    {
        static::assertSame(CommentUpdated::class, CommentUpdatedMailNotificationHandler::accepts());
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentUpdated(4, 123, 456, 'file', 'message', 'comment'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleCommentNoMentions(): void
    {
        $comment = new Comment();
        $comment->setMessage('comment1');

        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mentionService->expects(self::once())->method('getMentionedUsers')->with('comment1')->willReturn([]);
        $this->handler->handle(new CommentUpdated(4, 123, 456, 'file', 'message', 'comment2'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleNoNewMentions(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setReview($review);
        $comment->setMessage('comment2');
        $user = new User();

        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mentionService->expects(self::exactly(2))
            ->method('getMentionedUsers')
            ->with(...consecutive(['comment2'], ['comment1']))
            ->willReturn([$user], [$user]);
        $this->mailService->expects(self::never())->method('sendNewCommentReplyMail');

        $this->handler->handle(new CommentUpdated(5, 123, 456, 'file', 'message', 'comment1'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandle(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setMessage('comment2');
        $comment->setReview($review);
        $user = new User();

        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mentionService->expects(self::exactly(2))
            ->method('getMentionedUsers')
            ->with(...consecutive(['comment2'], ['comment1']))
            ->willReturn([$user], []);
        $this->mailService->expects(self::once())->method('sendNewCommentMail')->with($review, $comment, [$user]);

        $this->handler->handle(new CommentUpdated(5, 123, 456, 'file', 'message', 'comment1'));
    }
}
