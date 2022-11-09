<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewerService;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\CodeReviewerService
 * @covers ::__construct
 */
class CodeReviewerServiceTest extends AbstractTestCase
{
    private CodeReviewerService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new CodeReviewerService();
    }

    /**
     * @covers ::addReviewer
     */
    public function testAddReviewer(): void
    {
        $review = new CodeReview();
        $user   = new User();

        $reviewer = $this->service->addReviewer($review, $user);
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
        static::assertGreaterThan(1, $reviewer->getStateTimestamp());
        static::assertTrue($review->getReviewers()->contains($reviewer));
        static::assertSame($review, $reviewer->getReview());
        static::assertSame($user, $reviewer->getUser());
    }

    /**
     * @covers ::setReviewerState
     */
    public function testSetReviewerStateAccepted(): void
    {
        $comment = new Comment();
        $comment->setState(CommentStateType::OPEN);
        $review = new CodeReview();
        $review->getComments()->add($comment);

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);

        $this->service->setReviewerState($review, $reviewer, CodeReviewerStateType::ACCEPTED);
        static::assertSame(CodeReviewerStateType::ACCEPTED, $reviewer->getState());
        static::assertSame(CodeReviewStateType::CLOSED, $review->getState());
        static::assertSame(CommentStateType::RESOLVED, $comment->getState());
    }

    /**
     * @covers ::setReviewerState
     */
    public function testSetReviewerStateOpen(): void
    {
        $reviewer = new CodeReviewer();
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);

        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->service->setReviewerState($review, $reviewer, CodeReviewerStateType::OPEN);
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
    }
}
