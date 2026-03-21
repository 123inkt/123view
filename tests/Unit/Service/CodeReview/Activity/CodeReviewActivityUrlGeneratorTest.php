<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Activity;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(CodeReviewActivityUrlGenerator::class)]
class CodeReviewActivityUrlGeneratorTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject  $urlGenerator;
    private CommentReplyRepository&MockObject $replyRepository;
    private CodeReviewActivityUrlGenerator    $activityUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator         = $this->createMock(UrlGeneratorInterface::class);
        $this->replyRepository      = $this->createMock(CommentReplyRepository::class);
        $this->activityUrlGenerator = new CodeReviewActivityUrlGenerator($this->urlGenerator, $this->replyRepository);
    }

    public function testGenerateDefaultActivity(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setEventName(ReviewAccepted::NAME);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review])
            ->willReturn('url');
        $this->replyRepository->expects($this->never())->method('find');

        static::assertSame('url', $this->activityUrlGenerator->generate($activity));
    }

    public function testGenerateCommentActivity(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setData(['commentId' => 456, 'file' => 'filePath']);
        $activity->setEventName(CommentAdded::NAME);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review, 'filePath' => 'filePath'])
            ->willReturn('url');
        $this->replyRepository->expects($this->never())->method('find');

        static::assertSame('url#focus:comment:456', $this->activityUrlGenerator->generate($activity));
    }

    public function testGenerateReplyActivity(): void
    {
        $comment = new Comment();
        $comment->setFilePath('filePath');
        $reply = new CommentReply();
        $reply->setComment($comment);

        $review = new CodeReview();
        $review->setId(123);

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setData(['commentId' => 456]);
        $activity->setEventName(CommentReplyAdded::NAME);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review, 'filePath' => 'filePath'])
            ->willReturn('url');
        $this->replyRepository->expects($this->once())->method('find')->with(456)->willReturn($reply);

        static::assertSame('url#focus:reply:456', $this->activityUrlGenerator->generate($activity));
    }
}
