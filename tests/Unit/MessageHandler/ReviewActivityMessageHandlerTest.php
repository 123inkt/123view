<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\MessageHandler\ReviewActivityMessageHandler;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;
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
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvoke(): void
    {
        $event = new ReviewCreated(123, 456);

        $activity = new CodeReviewActivity();
        $this->activityProvider->expects(self::once())->method('fromEvent')->with($event)->willReturn($activity);
        $this->activityRepository->expects(self::once())->method('save')->with($activity, true);
        ($this->messageHandler)($event);
    }
}
