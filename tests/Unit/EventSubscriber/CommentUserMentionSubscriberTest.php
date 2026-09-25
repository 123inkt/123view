<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\EventSubscriber\CommentUserMentionSubscriber;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentUserMentionSubscriber::class)]
class CommentUserMentionSubscriberTest extends AbstractTestCase
{
    private CommentMentionService&MockObject $mentionService;
    private CommentUserMentionSubscriber     $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mentionService = $this->createMock(CommentMentionService::class);
        $this->service        = new CommentUserMentionSubscriber($this->mentionService);
    }

    public function testPostUpdateWithComment(): void
    {
        $comment = new Comment();

        $this->mentionService->expects($this->once())->method('updateMentions')->with($comment);

        $this->service->commentUpdated($comment);
    }

    public function testPostUpdateWithReply(): void
    {
        $comment = new Comment();
        $reply   = new CommentReply();
        $reply->setComment($comment);

        $this->mentionService->expects($this->once())->method('updateMentions')->with($comment);

        $this->service->commentReplyUpdated($reply);
    }
}
