<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\MessageHandler\ReviewActivityMessageHandler;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\ReviewActivityMessageHandler
 * @covers ::__construct
 */
class ReviewActivityMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewActivityProvider&MockObject   $activityProvider;
    private CodeReviewActivityRepository&MockObject $activityRepository;
    private ReviewActivityMessageHandler            $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->activityProvider   = $this->createMock(CodeReviewActivityProvider::class);
        $this->activityRepository = $this->createMock(CodeReviewActivityRepository::class);
        $this->messageHandler     = new ReviewActivityMessageHandler($this->activityProvider, $this->activityRepository);
    }

    /**
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeWithUnsupportedEvent(): void
    {
        $event = $this->createMock(CodeReviewAwareInterface::class);

        $this->activityRepository->expects(self::never())->method('save');
        ($this->messageHandler)($event);
    }

    /**
     * @dataProvider eventDataProvider
     * @covers ::__invoke
     * @covers ::getActivity
     * @throws Throwable
     */
    public function testInvoke(CodeReviewAwareInterface $event, string $expectedMethod): void
    {
        $activity = new CodeReviewActivity();
        $this->activityProvider->expects(self::once())->method($expectedMethod)->with($event)->willReturn($activity);
        $this->activityRepository->expects(self::once())->method('save')->with($activity, true);
        ($this->messageHandler)($event);
    }

    /**
     * @return Generator<array<CodeReviewAwareInterface|string>>
     */
    public function eventDataProvider(): Generator
    {
        yield [new ReviewCreated(123), 'fromReviewCreated'];
        yield [new ReviewAccepted(123, 456), 'fromReviewEvent'];
        yield [new ReviewRejected(123, 456), 'fromReviewEvent'];
        yield [new ReviewOpened(123, 456), 'fromReviewEvent'];
        yield [new ReviewResumed(123, 456), 'fromReviewEvent'];
        yield [new ReviewClosed(123, 456), 'fromReviewEvent'];
        yield [new ReviewerAdded(123, 456, 789), 'fromReviewerEvent'];
        yield [new ReviewerRemoved(123, 456, 789), 'fromReviewerEvent'];
        yield [new ReviewRevisionAdded(123, 456, 789), 'fromReviewRevisionEvent'];
        yield [new ReviewRevisionRemoved(123, 456, 789), 'fromReviewRevisionEvent'];
        yield [new CommentAdded(123, 456), 'fromCommentEvent'];
        yield [new CommentUpdated(123, 456, 'message'), 'fromCommentEvent'];
        yield [new CommentReplyAdded(123, 456), 'fromCommentReplyEvent'];
        yield [new CommentReplyUpdated(123, 456, 'message'), 'fromCommentReplyEvent'];
    }
}
