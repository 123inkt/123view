<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Service\Mail\MailRecipientService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailRecipientService
 * @covers ::__construct
 */
class MailRecipientServiceTest extends AbstractTestCase
{
    private UserRepository&MockObject        $userRepository;
    private CommentMentionService&MockObject $mentionService;
    private MailRecipientService             $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->mentionService = $this->createMock(CommentMentionService::class);
        $this->service        = new MailRecipientService($this->userRepository, $this->mentionService);
    }

    /**
     * @covers ::getUsersForReply
     */
    public function testGetUsersForReply(): void
    {
        $userA = new User();
        $userB = new User();
        $userC = new User();

        $replyA = new CommentReply();
        $replyA->setUser($userA);
        $replyB = new CommentReply();
        $replyB->setUser($userB);
        $replyC = new CommentReply();
        $replyC->setUser($userC);

        $comment = new Comment();
        $comment->getReplies()->add($replyA);
        $comment->getReplies()->add($replyB);
        $comment->getReplies()->add($replyC);

        $this->mentionService->expects(self::exactly(2))->method('getMentionedUsers')->willReturnOnConsecutiveCalls([], [$userA]);

        $users = $this->service->getUsersForReply($comment, $replyB);
        static::assertCount(3, $users);
        static::assertSame([$userA, $userB, $userA], $users);
    }

    /**
     * @covers ::getUserForComment
     */
    public function testGetUserForComment(): void
    {
    }

    /**
     * @covers ::getUsersForReview
     */
    public function testGetUsersForReview(): void
    {
    }

    /**
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForRevisions(): void
    {
    }
}
