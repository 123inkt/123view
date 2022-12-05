<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\MessageHandler\ReviewActivityMessageHandler;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Service\CodeReview\CodeReviewActivityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\ReviewActivityMessageHandler
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
