<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
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
     * @covers ::fromReviewEvent
     */
    public function testFromReviewEvent(): void
    {
    }

    /**
     * @covers ::fromCommentEvent
     */
    public function testFromCommentEvent(): void
    {
    }

    /**
     * @covers ::fromReviewRevisionEvent
     */
    public function testFromReviewRevisionEvent(): void
    {
    }

    /**
     * @covers ::fromCommentReplyEvent
     */
    public function testFromCommentReplyEvent(): void
    {
    }

    /**
     * @covers ::fromReviewerEvent
     */
    public function testFromReviewerEvent(): void
    {
    }
}
