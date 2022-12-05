<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Service\Mail\MailRecipientService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Mail\MailRecipientService
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
        $userA = new User();
        $userB = new User();
        $userC = new User();

        $comment = new Comment();
        $comment->setUser($userA);

        $this->mentionService->expects(self::once())->method('getMentionedUsers')->willReturn([$userB, $userC]);

        $users = $this->service->getUserForComment($comment);
        static::assertSame([$userA, $userB, $userC], $users);
    }

    /**
     * @covers ::getUsersForReview
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForReview(): void
    {
        $userA = new User();
        $userB = new User();
        $userC = new User();

        $reviewerA = new CodeReviewer();
        $reviewerA->setUser($userA);
        $reviewerB = new CodeReviewer();
        $reviewerB->setUser($userB);

        $revision = new Revision();
        $revision->setAuthorEmail('sherlock@example.com');

        $review = new CodeReview();
        $review->getReviewers()->add($reviewerA);
        $review->getReviewers()->add($reviewerB);
        $review->getRevisions()->add($revision);

        $this->userRepository->expects(self::once())->method('findBy')->with(['email' => ['sherlock@example.com']])->willReturn([$userC]);

        $users = $this->service->getUsersForReview($review);
        static::assertSame([$userC, $userA, $userB], $users);
    }

    /**
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForRevisions(): void
    {
        $userA    = new User();
        $revision = new Revision();
        $revision->setAuthorEmail('sherlock@example.com');

        $this->userRepository->expects(self::once())->method('findBy')->with(['email' => ['sherlock@example.com']])->willReturn([$userA]);

        $users = $this->service->getUsersForRevisions([$revision]);
        static::assertSame([$userA], $users);
    }

    /**
     * @covers ::getUsersForRevisions
     */
    public function testGetUsersForRevisionsWithoutEmail(): void
    {
        $this->userRepository->expects(self::never())->method('findBy');

        static::assertSame([], $this->service->getUsersForRevisions([]));
    }
}
