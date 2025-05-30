<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentReplyAddedMailNotificationHandler::class)]
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

    public function testAccepts(): void
    {
        static::assertSame(CommentReplyAdded::class, CommentReplyAddedMailNotificationHandler::accepts());
    }

    /**
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->replyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message', 'file'));
    }

    /**
     * @throws Throwable
     */
    public function testHandleCommentStatusAlreadyHandled(): void
    {
        $comment = new CommentReply();
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);

        $this->replyRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message', 'file'));
    }

    /**
     * @throws Throwable
     */
    public function testHandle(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setReview($review);
        $reply = new CommentReply();
        $reply->setComment($comment);

        $this->replyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->mailService->expects($this->once())->method('sendNewCommentReplyMail')->with($review, $comment, $reply);
        $this->replyRepository->expects($this->once())->method('save')->with($reply, true);

        $this->handler->handle(new CommentReplyAdded(5, 123, 456, 'message', 'file'));

        static::assertTrue($reply->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED));
    }
}
