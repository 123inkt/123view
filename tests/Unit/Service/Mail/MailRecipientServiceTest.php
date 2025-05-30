<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mail;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Service\Mail\MailRecipientService;
use DR\Review\Service\User\UserService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(MailRecipientService::class)]
class MailRecipientServiceTest extends AbstractTestCase
{
    private UserService&MockObject               $userService;
    private CommentMentionService&MockObject     $mentionService;
    private CodeReviewRevisionService&MockObject $revisionService;
    private MailRecipientService                 $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->userService     = $this->createMock(UserService::class);
        $this->mentionService  = $this->createMock(CommentMentionService::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->service         = new MailRecipientService($this->userService, $this->mentionService, $this->revisionService);
    }

    public function testGetUsersForReply(): void
    {
        $userA = new User();
        $userB = new User();
        $userC = new User();

        $replyA = new CommentReply();
        $replyA->setUser($userA);
        $replyA->setMessage('message');
        $replyB = new CommentReply();
        $replyB->setUser($userB);
        $replyB->setMessage('message');
        $replyC = new CommentReply();
        $replyC->setUser($userC);
        $replyC->setMessage('message');

        $comment = new Comment();
        $comment->getReplies()->add($replyA);
        $comment->getReplies()->add($replyB);
        $comment->getReplies()->add($replyC);

        $this->mentionService->expects($this->exactly(2))->method('getMentionedUsers')->willReturnOnConsecutiveCalls([], [$userA]);

        $users = $this->service->getUsersForReply($comment, $replyB);
        static::assertCount(3, $users);
        static::assertSame([$userA, $userB, $userA], $users);
    }

    public function testGetUserForComment(): void
    {
        $userA = new User();
        $userB = new User();
        $userC = new User();

        $comment = new Comment();
        $comment->setUser($userA);
        $comment->setMessage('message');

        $this->mentionService->expects($this->once())->method('getMentionedUsers')->willReturn([$userB, $userC]);

        $users = $this->service->getUserForComment($comment);
        static::assertSame([$userA, $userB, $userC], $users);
    }

    /**
     * @throws RepositoryException
     */
    public function testGetUsersForReview(): void
    {
        $userA = new User();
        $userA->setId(123);
        $userB = new User();
        $userB->setId(456);
        $userC = new User();
        $userC->setId(789);

        $reviewerA = new CodeReviewer();
        $reviewerA->setUser($userA);
        $reviewerB = new CodeReviewer();
        $reviewerB->setUser($userB);

        $revision = new Revision();
        $revision->setAuthorEmail('sherlock@example.com');

        $review = new CodeReview();
        $review->getReviewers()->add($reviewerA);
        $review->getReviewers()->add($reviewerB);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->userService->expects($this->once())->method('getUsersForRevisions')->with([$revision])->willReturn([$userC]);

        $users = $this->service->getUsersForReview($review);
        static::assertSame([$userC, $userA, $userB], $users);
    }
}
