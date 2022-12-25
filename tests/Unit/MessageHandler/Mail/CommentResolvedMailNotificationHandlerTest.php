<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Mail;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Mail\CommentMailService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\Mail\CommentResolvedMailNotificationHandler
 * @covers ::__construct
 */
class CommentResolvedMailNotificationHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject          $mailService;
    private CommentRepository&MockObject           $commentRepository;
    private UserRepository&MockObject              $userRepository;
    private CommentResolvedMailNotificationHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailService       = $this->createMock(CommentMailService::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->userRepository    = $this->createMock(UserRepository::class);
        $this->handler           = new CommentResolvedMailNotificationHandler($this->mailService, $this->commentRepository, $this->userRepository);
    }

    /**
     * @covers ::accepts
     */
    public function testAccepts(): void
    {
        static::assertSame(CommentResolved::class, CommentResolvedMailNotificationHandler::accepts());
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentResolved(4, 123, 5, 'file'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleCommentStatusAlreadyHandled(): void
    {
        $comment = new Comment();
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_RESOLVED);
        $comment->setState(CommentStateType::RESOLVED);
        $user = new User();

        $this->userRepository->expects(self::once())->method('find')->with(6)->willReturn($user);
        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mailService->expects(self::never())->method('sendCommentResolvedMail');
        $this->handler->handle(new CommentResolved(5, 123, 6, 'file'));
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
        $comment->setState(CommentStateType::RESOLVED);

        $user = new User();

        $this->userRepository->expects(self::once())->method('find')->with(6)->willReturn($user);
        $this->commentRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mailService->expects(self::once())->method('sendCommentResolvedMail')->with($review, $comment);
        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);

        $this->handler->handle(new CommentResolved(5, 123, 6, 'file'));

        static::assertTrue($comment->getNotificationStatus()->hasStatus(NotificationStatus::STATUS_RESOLVED));
    }
}
