<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Service\CodeReview\Comment\ActivityCommentProvider;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(ReviewTimelineViewModelProvider::class)]
class ReviewTimelineViewModelProviderTest extends AbstractTestCase
{
    private CodeReviewActivityRepository&MockObject   $activityRepository;
    private CodeReviewActivityFormatter&MockObject    $activityFormatter;
    private ActivityCommentProvider&MockObject        $commentProvider;
    private CodeReviewActivityUrlGenerator&MockObject $urlGenerator;
    private UserEntityProvider&MockObject             $userProvider;
    private ReviewTimelineViewModelProvider           $provider;
    private User                                      $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user               = new User();
        $this->userProvider       = $this->createMock(UserEntityProvider::class);
        $this->activityRepository = $this->createMock(CodeReviewActivityRepository::class);
        $this->activityFormatter  = $this->createMock(CodeReviewActivityFormatter::class);
        $this->commentProvider    = $this->createMock(ActivityCommentProvider::class);
        $this->urlGenerator       = $this->createMock(CodeReviewActivityUrlGenerator::class);
        $this->provider           = new ReviewTimelineViewModelProvider(
            $this->activityRepository,
            $this->activityFormatter,
            $this->commentProvider,
            $this->urlGenerator,
            $this->userProvider
        );
    }

    public function testGetTimelineViewModel(): void
    {
        $activityA = new CodeReviewActivity();
        $activityA->setEventName('event');
        $activityB = new CodeReviewActivity();
        $activityB->setEventName('event');
        $review = new CodeReview();
        $review->setId(123);

        $this->userProvider->expects($this->exactly(2))
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->activityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activityA, $activityB]);
        $this->activityFormatter->expects($this->exactly(2))
            ->method('format')
            ->with(...consecutive([$activityA, $this->user], [$activityB, $this->user]))
            ->willReturn('message', null);

        $viewModel = $this->provider->getTimelineViewModel($review, []);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame([$activityA], $timeline->activities);
        static::assertSame('message', $timeline->message);
    }

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

        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->activityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activity]);
        $this->activityFormatter->expects($this->once())
            ->method('format')
            ->with($activity, $this->user)
            ->willReturn('message');

        $viewModel = $this->provider->getTimelineViewModel($review, []);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame($comment, $timeline->getComment());
    }

    public function testGetTimelineViewModelWithRevision(): void
    {
        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewRevisionAdded::NAME);
        $activity->setData(['revisionId' => 456]);
        $revision = new Revision();
        $revision->setId(456);
        $review = new CodeReview();
        $review->setId(123);

        $this->userProvider->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->user);
        $this->activityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123], ['createTimestamp' => 'ASC'])
            ->willReturn([$activity]);
        $this->activityFormatter->expects($this->once())
            ->method('format')
            ->with($activity, $this->user)
            ->willReturn('message');

        $viewModel = $this->provider->getTimelineViewModel($review, [456 => $revision]);
        static::assertCount(1, $viewModel->entries);

        $timeline = $viewModel->entries[0];
        static::assertSame($revision, $timeline->getRevision());
    }

    public function testGetTimelineViewModelForFeedShouldSkip(): void
    {
        $user     = (new User())->setId(789);
        $activity = (new CodeReviewActivity())->setEventName(CommentAdded::NAME);

        $this->activityRepository->expects($this->once())->method('findForUser')->with(789, [CommentAdded::NAME])->willReturn([$activity]);
        $this->activityFormatter->expects($this->once())->method('format')->with($activity, $user)->willReturn(null);
        $this->commentProvider->expects($this->never())->method('getCommentFor');
        $this->urlGenerator->expects($this->never())->method('generate');

        $viewModel = $this->provider->getTimelineViewModelForFeed($user, [CommentAdded::NAME]);
        static::assertCount(0, $viewModel->entries);
    }

    public function testGetTimelineViewModelForFeed(): void
    {
        $user     = (new User())->setId(789);
        $activity = (new CodeReviewActivity())->setEventName(CommentAdded::NAME);
        $comment  = new Comment();

        $this->activityRepository->expects($this->once())->method('findForUser')->with(789, [CommentAdded::NAME])->willReturn([$activity]);
        $this->activityFormatter->expects($this->once())->method('format')->with($activity, $user)->willReturn('activityA');
        $this->commentProvider->expects($this->once())->method('getCommentFor')->with($activity)->willReturn($comment);
        $this->urlGenerator->expects($this->once())->method('generate')->with($activity)->willReturn('url');

        $viewModel = $this->provider->getTimelineViewModelForFeed($user, [CommentAdded::NAME]);
        static::assertCount(1, $viewModel->entries);
        static::assertSame($comment, $viewModel->entries[0]->getComment());
    }
}
