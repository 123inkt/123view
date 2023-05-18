<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider
 * @covers ::__construct
 */
class ReviewTimelineViewModelProviderTest extends AbstractTestCase
{
    private CodeReviewActivityRepository&MockObject   $activityRepository;
    private CodeReviewActivityFormatter&MockObject    $activityFormatter;
    private CommentRepository&MockObject              $commentRepository;
    private CodeReviewActivityUrlGenerator&MockObject $urlGenerator;
    private ReviewTimelineViewModelProvider           $provider;
    private User                                      $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user               = new User();
        $this->activityRepository = $this->createMock(CodeReviewActivityRepository::class);
        $this->activityFormatter  = $this->createMock(CodeReviewActivityFormatter::class);
        $this->commentRepository  = $this->createMock(CommentRepository::class);
        $this->urlGenerator       = $this->createMock(CodeReviewActivityUrlGenerator::class);
        $this->provider           = new ReviewTimelineViewModelProvider(
            $this->activityRepository,
            $this->activityFormatter,
            $this->commentRepository,
            $this->urlGenerator,
            $this->user
        );
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
            ->will(static::onConsecutiveCalls([$activityA, $this->user], [$activityA, $this->user]))
            ->willReturn('message', null);

        $viewModel = $this->provider->getTimelineViewModel($review);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame([$activityA], $timeline->activities);
        static::assertSame('message', $timeline->message);
    }

    /**
     * @covers ::getTimelineViewModel
     */
    public function testGetTimelineViewModelWithComment(): void
    {
        $activity = new CodeReviewActivity();
        $activity->setEventName(CommentAdded::NAME);
        $activity->setData(['commentId' => 456]);
        $comment = new Comment();
        $comment->setId(456);
        $review = new CodeReview();
        $review->setId(123);
        $review->getComments()->set(456, $comment);

        $this->activityRepository->expects(self::once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activity]);
        $this->activityFormatter->expects(self::once())
            ->method('format')
            ->with($activity, $this->user)
            ->willReturn('message');

        $viewModel = $this->provider->getTimelineViewModel($review, []);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame($comment, $timeline->getComment());
    }

    /**
     * @covers ::getTimelineViewModel
     */
    public function testGetTimelineViewModelWithRevision(): void
    {
        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewRevisionAdded::NAME);
        $activity->setData(['revisionId' => 456]);
        $revision = new Revision();
        $revision->setId(456);
        $review = new CodeReview();
        $review->setId(123);
        $review->getRevisions()->set(456, $revision);

        $this->activityRepository->expects(self::once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activity]);
        $this->activityFormatter->expects(self::once())
            ->method('format')
            ->with($activity, $this->user)
            ->willReturn('message');

        $viewModel = $this->provider->getTimelineViewModel($review);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame($revision, $timeline->getRevision());
    }

    /**
     * @covers ::getTimelineViewModelForFeed
     */
    public function testGetTimelineViewModelForFeed(): void
    {
        $user = new User();
        $user->setId(789);
        $activityA = new CodeReviewActivity();
        $activityA->setEventName(CommentAdded::NAME);
        $activityA->setData(['commentId' => 456]);
        $activityB = new CodeReviewActivity();
        $activityC = new CodeReviewActivity();
        $activityC->setEventName(ReviewAccepted::NAME);
        $review = new CodeReview();
        $review->setId(123);

        $this->activityRepository->expects(self::once())
            ->method('findForUser')
            ->with(789, [CommentAdded::NAME])
            ->willReturn([$activityA, $activityB, $activityC]);
        $this->activityFormatter->expects(self::exactly(3))
            ->method('format')
            ->will(static::onConsecutiveCalls([$activityA, $user], [$activityB, $user]))
            ->willReturn('activityA', null, 'activityC');
        $this->commentRepository->expects(self::once())->method('find')->with(456)->willReturn(null);
        $this->urlGenerator->expects(self::once())->method('generate')->will(static::onConsecutiveCalls([$activityC]))->willReturn('url');

        $viewModel = $this->provider->getTimelineViewModelForFeed($user, [CommentAdded::NAME]);
        static::assertCount(1, $viewModel->entries);
    }
}
