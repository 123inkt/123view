<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
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
        $config->setExcludedMethods(['getWatchers', 'getReviewers', 'getRevisions']);
        static::assertAccessorPairs(CodeReview::class, $config);
    }

    /**
     * @covers ::getRevisions
     * @covers ::setRevisions
     */
    public function testRevisions(): void
    {
        $collection = new ArrayCollection();

        $repository = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $repository->getRevisions());

        $repository->setRevisions($collection);
        static::assertSame($collection, $repository->getRevisions());
    }

    /**
     * @covers ::getReviewers
     * @covers ::setReviewers
     */
    public function testReviewers(): void
    {
        $collection = new ArrayCollection();

        $repository = new User();
        static::assertInstanceOf(ArrayCollection::class, $repository->getReviewers());

        $repository->setReviewers($collection);
        static::assertSame($collection, $repository->getReviewers());
    }

    /**
     * @covers ::getWatchers
     * @covers ::setWatchers
     */
    public function testWatchers(): void
    {
        $collection = new ArrayCollection();

        $repository = new CodeReview();
        static::assertInstanceOf(ArrayCollection::class, $repository->getWatchers());

        $repository->setWatchers($collection);
        static::assertSame($collection, $repository->getWatchers());
    }
}
