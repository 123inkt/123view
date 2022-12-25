<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\CodeReviewActivityPublisher;
use DR\Review\Tests\AbstractTestCase;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\CodeReviewActivityPublisher
 * @covers ::__construct
 */
class CodeReviewActivityPublisherTest extends AbstractTestCase
{
    private CodeReviewActivityFormatter&MockObject $formatter;
    private HubInterface&MockObject                $mercureHub;
    private CodeReviewActivityPublisher            $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->formatter  = $this->createMock(CodeReviewActivityFormatter::class);
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->service    = new CodeReviewActivityPublisher($this->formatter, $this->mercureHub);
    }

    /**
     * @covers ::publish
     * @throws JsonException
     */
    public function testPublishNoMessageNoPublish(): void
    {
        $activity = new CodeReviewActivity();

        $this->formatter->expects(self::once())->method('format')->with($activity)->willReturn(null);
        $this->mercureHub->expects(self::never())->method('publish');

        $this->service->publish($activity);
    }

    /**
     * @covers ::publish
     * @throws JsonException
     */
    public function testPublish(): void
    {
        $user = new User();
        $user->setId(123);
        $review = new CodeReview();
        $review->setId(456);
        $activity = new CodeReviewActivity();
        $activity->setEventName('event');
        $activity->setUser($user);
        $activity->setReview($review);

        $update = new Update(
            '/review/456',
            json_encode(['userId' => 123, 'reviewId' => 456, 'eventName' => 'event', 'message' => 'message'], JSON_THROW_ON_ERROR),
            true
        );

        $this->formatter->expects(self::once())->method('format')->with($activity)->willReturn('message');
        $this->mercureHub->expects(self::once())->method('publish')->with($update);

        $this->service->publish($activity);
    }
}
