<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler\Mail;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Service\Mail\CommentMailService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler
 * @covers ::__construct
 */
class CommentReplyUpdatedMailNotificationHandlerTest extends AbstractTestCase
{
    private CommentMailService&MockObject              $mailService;
    private CommentReplyRepository&MockObject          $replyRepository;
    private CommentMentionService&MockObject           $mentionService;
    private CommentReplyUpdatedMailNotificationHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailService     = $this->createMock(CommentMailService::class);
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->mentionService  = $this->createMock(CommentMentionService::class);
        $this->handler         = new CommentReplyUpdatedMailNotificationHandler($this->mailService, $this->replyRepository, $this->mentionService);
    }

    /**
     * @covers ::accepts
     */
    public function testAccepts(): void
    {
        static::assertSame(CommentReplyUpdated::class, CommentReplyUpdatedMailNotificationHandler::accepts());
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleAbsentCommentShouldReturnEarly(): void
    {
        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->handler->handle(new CommentReplyUpdated(4, 123, 456, 'comment'));
    }

    /**
     * @covers ::handle
     * @throws Throwable
     */
    public function testHandleCommentNoMentions(): void
    {
        $comment = new CommentReply();
        $comment->setMessage('comment1');

        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn($comment);
        $this->mentionService->expects(self::once())->method('getMentionedUsers')->with('comment1')->willReturn([]);
        $this->handler->handle(new CommentReplyUpdated(4, 123, 456, 'comment2'));
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
        $reply = new CommentReply();
        $reply->setMessage('comment2');
        $reply->setComment($comment);
        $user = new User();

        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn($reply);
        $this->mentionService->expects(self::exactly(2))
            ->method('getMentionedUsers')
            ->withConsecutive(['comment2'], ['comment1'])
            ->willReturn([$user], [$user]);
        $this->mailService->expects(self::never())->method('sendNewCommentReplyMail');

        $this->handler->handle(new CommentReplyUpdated(5, 123, 456, 'comment1'));
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
        $reply->setMessage('comment2');
        $reply->setComment($comment);
        $user = new User();

        $this->replyRepository->expects(self::once())->method('find')->with(123)->willReturn($reply);
        $this->mentionService->expects(self::exactly(2))
            ->method('getMentionedUsers')
            ->withConsecutive(['comment2'], ['comment1'])
            ->willReturn([$user], []);
        $this->mailService->expects(self::once())->method('sendNewCommentReplyMail')->with($review, $comment, $reply);

        $this->handler->handle(new CommentReplyUpdated(5, 123, 456, 'comment1'));
    }
}
