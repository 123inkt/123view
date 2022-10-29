<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Review\CodeReview
 * @covers ::__construct
 */
class CodeReviewTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['getWatchers', 'getReviewers', 'getRevisions', 'getComments']);
        static::assertAccessorPairs(CodeReview::class, $config);
    }

    /**
     * @covers ::getRevisions
     * @covers ::setRevisions
     * @covers ::addRevision
     */
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

    /**
     * @covers ::isAccepted
     * @covers ::getReviewersState
     */
    public function testIsAccepted(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);
        static::assertFalse($review->isAccepted());

        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        static::assertTrue($review->isAccepted());
    }

    /**
     * @covers ::isRejected
     * @covers ::getReviewersState
     */
    public function testIsRejected(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $review->getReviewersState());

        $reviewer = new CodeReviewer();
        $review->getReviewers()->add($reviewer);
        static::assertFalse($review->isRejected());

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        static::assertTrue($review->isRejected());
        static::assertSame(CodeReviewerStateType::REJECTED, $review->getReviewersState());
    }

    /**
     * @covers ::getReviewer
     */
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

    /**
     * @covers ::getReviewers
     * @covers ::setReviewers
     */
    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $review = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $review->getReviewers());

        $review->setReviewers($collection);
        static::assertSame($collection, $review->getReviewers());
    }

    /**
     * @covers ::getComments
     * @covers ::setComments
     */
    public function testComments(): void
    {
        $collection = new ArrayCollection();

        $review = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $review->getComments());

        $review->setComments($collection);
        static::assertSame($collection, $review->getComments());
    }
}
