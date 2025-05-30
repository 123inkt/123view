<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentAddedMailNotificationHandler::class)]
class CommentAddedMailNotificationHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject       $mailService;
    private CommentRepository&MockObject        $commentRepository;
    private CommentAddedMailNotificationHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailService       = $this->createMock(CommentMailService::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->handler           = new CommentAddedMailNotificationHandler($this->mailService, $this->commentRepository);
    }

    public function testAccepts(): void
    {
        static::assertSame(CommentAdded::class, CommentAddedMailNotificationHandler::accepts());
    }

    /**
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentAdded(5, 123, 456, 'file', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testHandleCommentStatusAlreadyHandled(): void
    {
        $comment = new Comment();
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->handler->handle(new CommentAdded(5, 123, 456, 'file', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testHandle(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setReview($review);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->mailService->expects($this->once())->method('sendNewCommentMail')->with($review, $comment);
        $this->commentRepository->expects($this->once())->method('save')->with($comment, true);

        $this->handler->handle(new CommentAdded(5, 123, 456, 'file', 'message'));

        static::assertTrue($comment->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED));
    }
}
