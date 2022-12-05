<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler\Mail;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler
 * @covers ::__construct
 */
class CommentReplyAddedMailNotificationHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject            $mailService;
    private CommentReplyRepository&MockObject        $replyRepository;
    private CommentReplyAddedMailNotificationHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailService     = $this->createMock(CommentMailService::class);
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->handler         = new CommentReplyAddedMailNotificationHandler($this->mailService, $this->replyRepository);
    }

    /**
     * @covers ::accepts
     */
    public function testAccepts(): void
    {
        static::assertSame(CommentReplyAdded::class, CommentReplyAddedMailNotificationHandler::accepts());
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleCommentStatusAlreadyHandled(): void
    {
        $comment = new CommentReply();
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);

        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandle(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setReview($review);
        $reply = new CommentReply();
        $reply->setComment($comment);

        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn($reply);
        $this->mailService->expects(self::once())->method('sendNewCommentReplyMail')->with($review, $comment, $reply);
        $this->replyRepository->expects(self::once())->method('save')->with($reply, true);

        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message'));

        static::assertTrue($reply->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED));
    }
}
