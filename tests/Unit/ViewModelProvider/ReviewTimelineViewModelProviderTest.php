<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\ReviewTimelineViewModelProvider
 * @covers ::__construct
 */
class ReviewTimelineViewModelProviderTest extends AbstractTestCase
{
    private CodeReviewActivityRepository&MockObject $activityRepository;
    private CodeReviewActivityFormatter&MockObject  $activityFormatter;
    private ReviewTimelineViewModelProvider         $provider;
    private User                                    $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user               = new User();
        $this->activityRepository = $this->createMock(CodeReviewActivityRepository::class);
        $this->activityFormatter  = $this->createMock(CodeReviewActivityFormatter::class);
        $this->provider           = new ReviewTimelineViewModelProvider($this->activityRepository, $this->activityFormatter, $this->user);
    }

    /**
     * @covers ::getTimelineViewModel
     */
    public function testGetTimelineViewModel(): void
    {
        $activityA = new CodeReviewActivity();
        $activityB = new CodeReviewActivity();
        $review    = new CodeReview();
        $review->setId(123);

        $this->activityRepository->expects(self::once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activityA, $activityB]);
        $this->activityFormatter->expects(self::exactly(2))
            ->method('format')
            ->withConsecutive([$this->user, $activityA], [$this->user, $activityA])
            ->willReturn('message', null);

        $viewModel = $this->provider->getTimelineViewModel($review);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame([$activityA], $timeline->activities);
        static::assertSame('message', $timeline->message);
    }
}
