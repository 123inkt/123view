<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReview::class)]
class CodeReviewTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getReviewers', 'getRevisions', 'getComments']);
        static::assertAccessorPairs(CodeReview::class, $config);
    }

    public function testId(): void
    {
        $review = new CodeReview();
        static::assertFalse($review->hasId());

        $review->setId(123);
        static::assertSame(123, $review->getId());
    }

    public function testRevisions(): void
    {
        /** @var ArrayCollection<int, Revision> $collection */
        $collection = new ArrayCollection();

        $review = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $review->getRevisions());

        $review->setRevisions($collection);
        static::assertSame($collection, $review->getRevisions());

        $revision = new Revision();
        $review->addRevision($revision);
        static::assertSame($review, $revision->getReview());
        static::assertSame($revision, $collection->first());
    }

    public function testIsAccepted(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        static::assertSame(CodeReviewerStateType::ACCEPTED, $review->getReviewersState());
    }

    public function testIsRejected(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        static::assertSame(CodeReviewerStateType::REJECTED, $review->getReviewersState());
    }

    public function testGetReviewer(): void
    {
        $userA = new User();
        $userA->setId(5);
        $userB = new User();
        $userB->setId(6);
        $reviewer = new CodeReviewer();
        $reviewer->setUser($userA);

        $review = new CodeReview();
        $review->getReviewers()->add($reviewer);

        static::assertSame($reviewer, $review->getReviewer($userA));
        static::assertNull($review->getReviewer($userB));
    }

    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $review = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $review->getReviewers());

        $review->setReviewers($collection);
        static::assertSame($collection, $review->getReviewers());
    }

    public function testComments(): void
    {
        $collection = new ArrayCollection();

        $review = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $review->getComments());

        $review->setComments($collection);
        static::assertSame($collection, $review->getComments());
    }
}
