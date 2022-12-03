<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider
 * @covers ::__construct
 */
class CodeReviewActivityProviderTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject   $reviewRepository;
    private CommentRepository&MockObject      $commentRepository;
    private CommentReplyRepository&MockObject $replyRepository;
    private RevisionRepository&MockObject     $revisionRepository;
    private UserRepository&MockObject         $userRepository;
    private CodeReviewActivityProvider        $activityProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository   = $this->createMock(CodeReviewRepository::class);
        $this->commentRepository  = $this->createMock(CommentRepository::class);
        $this->replyRepository    = $this->createMock(CommentReplyRepository::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->userRepository     = $this->createMock(UserRepository::class);
        $this->activityProvider   = new CodeReviewActivityProvider(
            $this->reviewRepository,
            $this->commentRepository,
            $this->replyRepository,
            $this->revisionRepository,
            $this->userRepository
        );
    }

    /**
     * @covers ::fromReviewCreated
     * @covers ::createActivity
     */
    public function testFromReviewCreatedWithoutRevision(): void
    {
        $event    = new ReviewCreated(10);
        $review   = new CodeReview();
        $revision = new Revision();
        $revision->setId(5);
        $revision->setCommitHash('hash');
        $review->getRevisions()->add($revision);

        $this->reviewRepository->expects(self::once())->method('find')->with(10)->willReturn($review);

        $activity = $this->activityProvider->fromReviewCreated($event);
        static::assertSame($review, $activity->getReview());
        static::assertNull($activity->getUser());
        static::assertSame('review-created', $activity->getEventName());
        static::assertSame(['revisionId' => 5, 'commit-hash' => 'hash'], $activity->getData());
    }

    /**
     * @covers ::fromReviewRevisionEvent
     * @covers ::createActivity
     */
    public function testFromReviewRevisionEventWithoutReview(): void
    {
        $event = new ReviewRevisionAdded(123, 456, 789);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn(null);

        static::assertNull($this->activityProvider->fromReviewRevisionEvent($event));
    }

    /**
     * @covers ::fromReviewRevisionEvent
     * @covers ::createActivity
     */
    public function testFromReviewRevisionEvent(): void
    {
        $event  = new ReviewRevisionAdded(123, 456, 789);
        $review = new CodeReview();
        $review->setId(123);
        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setId(456);
        $user = new User();
        $user->setId(789);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->revisionRepository->expects(self::once())->method('find')->with(456)->willReturn($revision);
        $this->userRepository->expects(self::once())->method('find')->with(789)->willReturn($user);

        $activity = $this->activityProvider->fromReviewRevisionEvent($event);
        static::assertSame($user, $activity->getUser());
        static::assertSame(['revisionId' => 456, 'commit-hash' => 'hash'], $activity->getData());
    }

    /**
     * @covers ::fromReviewEvent
     */
    public function testFromReviewEvent(): void
    {
        $event  = new ReviewOpened(123, 456);
        $review = new CodeReview();
        $review->setId(123);
        $user = new User();
        $user->setId(456);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects(self::once())->method('find')->with(456)->willReturn($user);

        $activity = $this->activityProvider->fromReviewEvent($event);
        static::assertSame($user, $activity->getUser());
    }

    /**
     * @covers ::fromReviewerEvent
     */
    public function testFromReviewerEvent(): void
    {
        $event  = new ReviewerAdded(123, 789, 456);
        $review = new CodeReview();
        $review->setId(123);
        $user = new User();
        $user->setId(456);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->userRepository->expects(self::once())->method('find')->with(456)->willReturn($user);

        $activity = $this->activityProvider->fromReviewerEvent($event);
        static::assertSame($user, $activity->getUser());
        static::assertSame(['userId' => 789], $activity->getData());
    }

    /**
     * @covers ::fromCommentEvent
     */
    public function testFromCommentEvent(): void
    {
        $event  = new CommentAdded(123, 456);
        $review = new CodeReview();
        $review->setId(123);
        $user    = new User();
        $comment = new Comment();
        $comment->setId(456);
        $comment->setUser($user);
        $comment->setMessage('message');
        $comment->setFilePath('filepath');

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->commentRepository->expects(self::once())->method('find')->with(456)->willReturn($comment);
        $this->userRepository->expects(self::never())->method('find');

        $activity = $this->activityProvider->fromCommentEvent($event);
        static::assertSame($user, $activity->getUser());
        static::assertSame(['message' => 'message', 'file' => 'filepath'], $activity->getData());
    }

    /**
     * @covers ::fromCommentReplyEvent
     */
    public function testFromCommentReplyEvent(): void
    {
        $event  = new CommentReplyAdded(123, 456);
        $review = new CodeReview();
        $review->setId(123);
        $user    = new User();
        $comment = new CommentReply();
        $comment->setId(456);
        $comment->setUser($user);
        $comment->setMessage('message');

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->replyRepository->expects(self::once())->method('find')->with(456)->willReturn($comment);
        $this->userRepository->expects(self::never())->method('find');

        $activity = $this->activityProvider->fromCommentReplyEvent($event);
        static::assertSame($user, $activity->getUser());
        static::assertSame(['message' => 'message'], $activity->getData());
    }
}
