<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Repository
 */
class RepositoryTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['addRepositoryProperty', 'getRepositoryProperties', 'getReviews', 'getRevisions']);

        static::assertNull((new Repository())->getId());
        static::assertAccessorPairs(Repository::class, $config);
    }

    /**
     * @covers ::getRepositoryProperty
     */
    public function testGetRepositoryProperty(): void
    {
        $repository = new Repository();
        $repository->addRepositoryProperty((new RepositoryProperty())->setName('property')->setValue('value'));

        static::assertNull($repository->getRepositoryProperty('foobar'));
        static::assertSame('value', $repository->getRepositoryProperty('property'));
    }

    /**
     * @covers ::addRepositoryProperty
     * @covers ::getRepositoryProperties
     * @covers ::removeRepositoryProperty
     */
    public function testRepositoryPropertyAccessors(): void
    {
        $property = new RepositoryProperty();

        $repository = new Repository();
        $repository->addRepositoryProperty($property);
        static::assertSame([$property], $repository->getRepositoryProperties()->getValues());

        $repository->removeRepositoryProperty($property);
        static::assertCount(0, $repository->getRepositoryProperties());
    }

    /**
     * @covers ::getRevisions
     * @covers ::setRevisions
     */
    public function testRevisions(): void
    {
        $collection = new ArrayCollection();

        $repository = new Repository();
        static::assertInstanceOf(ArrayCollection::class, $repository->getRevisions());

        $repository->setRevisions($collection);
        static::assertSame($collection, $repository->getRevisions());
    }

    /**
     * @covers ::getReviews
     * @covers ::setReviews
     */
    public function testReviews(): void
    {
        $collection = new ArrayCollection();

        $repository = new Repository();
        static::assertInstanceOf(ArrayCollection::class, $repository->getReviews());

        $repository->setReviews($collection);
        static::assertSame($collection, $repository->getReviews());
    }
}
