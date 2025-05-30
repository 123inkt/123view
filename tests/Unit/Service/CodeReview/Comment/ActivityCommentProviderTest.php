<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\ActivityCommentProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ActivityCommentProvider::class)]
class ActivityCommentProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $replyRepository;
    private ActivityCommentProvider           $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->replyRepository   = $this->createMock(CommentReplyRepository::class);
        $this->provider          = new ActivityCommentProvider($this->commentRepository, $this->replyRepository);
    }

    public function testGetCommentForCommentAdded(): void
    {
        $comment  = new Comment();
        $activity = (new CodeReviewActivity())->setEventName(CommentAdded::NAME)->setData(['commentId' => '123']);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        static::assertSame($comment, $this->provider->getCommentFor($activity));
    }

    public function testGetCommentForReplyAdded(): void
    {
        $reply    = new CommentReply();
        $activity = (new CodeReviewActivity())->setEventName(CommentReplyAdded::NAME)->setData(['commentId' => '123']);

        $this->replyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        static::assertSame($reply, $this->provider->getCommentFor($activity));
    }

    public function testGetCommentForUnknown(): void
    {
        $activity = (new CodeReviewActivity())->setEventName('unknown');
        static::assertNull($this->provider->getCommentFor($activity));
    }
}
