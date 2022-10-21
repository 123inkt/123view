<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Review\CodeReview;
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
}
