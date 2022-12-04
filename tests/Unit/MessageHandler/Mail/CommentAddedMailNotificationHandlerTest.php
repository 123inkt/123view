<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler\Mail;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\Mail\CommentAddedMailNotificationHandler
 * @covers ::__construct
 */
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

    /**
     * @covers ::accepts
     */
    public function testAccepts(): void
    {
        static::assertSame(CommentAdded::class, CommentAddedMailNotificationHandler::accepts());
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentAdded(5, 123, 'message'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleCommentStatusAlreadyHandled(): void
    {
        $comment = new Comment();
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);

        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->handler->handle(new CommentAdded(5, 123, 'message'));
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

        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mailService->expects(self::once())->method('sendNewCommentMail')->with($review, $comment);
        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);

        $this->handler->handle(new CommentAdded(5, 123, 'message'));

        static::assertTrue($comment->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_CREATED));
    }
}
