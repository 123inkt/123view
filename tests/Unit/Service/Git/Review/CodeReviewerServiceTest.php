<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewerService::class)]
class CodeReviewerServiceTest extends AbstractTestCase
{
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private CodeReviewerService                  $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->service               = new CodeReviewerService($this->reviewerStateResolver);
    }

    public function testAddReviewer(): void
    {
        $this->reviewerStateResolver->expects($this->never())->method('getReviewersState');
        $review = new CodeReview();
        $user   = new User();

        $reviewer = $this->service->addReviewer($review, $user);
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
        static::assertGreaterThan(1, $reviewer->getStateTimestamp());
        static::assertTrue($review->getReviewers()->contains($reviewer));
        static::assertSame($review, $reviewer->getReview());
        static::assertSame($user, $reviewer->getUser());
    }

    public function testSetReviewerStateAccepted(): void
    {
        $comment = new Comment();
        $comment->setState(CommentStateType::OPEN);
        $review = new CodeReview();
        $review->getComments()->add($comment);

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::ACCEPTED);

        $this->service->setReviewerState($review, $reviewer, CodeReviewerStateType::ACCEPTED);
        static::assertSame(CodeReviewerStateType::ACCEPTED, $reviewer->getState());
        static::assertSame(CodeReviewStateType::CLOSED, $review->getState());
        static::assertSame(CommentStateType::RESOLVED, $comment->getState());
    }

    public function testSetReviewerStateOpen(): void
    {
        $reviewer = new CodeReviewer();
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);

        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);
        $review->getReviewers()->add($reviewer);

        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);

        $this->service->setReviewerState($review, $reviewer, CodeReviewerStateType::OPEN);
        static::assertSame(CodeReviewerStateType::OPEN, $reviewer->getState());
        static::assertSame(CodeReviewStateType::OPEN, $review->getState());
    }
}
