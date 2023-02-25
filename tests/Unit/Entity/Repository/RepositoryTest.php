<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Repository\Repository
 */
class RepositoryTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::isActive
     * @covers ::setActive
     * @covers ::getName
     * @covers ::setName
     * @covers ::getDisplayName
     * @covers ::setDisplayName
     * @covers ::getMainBranchName
     * @covers ::setMainBranchName
     * @covers ::getUrl
     * @covers ::setUrl
     * @covers ::isFavorite
     * @covers ::setFavorite
     * @covers ::getUpdateRevisionsInterval
     * @covers ::setUpdateRevisionsInterval
     * @covers ::getUpdateRevisionsTimestamp
     * @covers ::setUpdateRevisionsTimestamp
     * @covers ::getValidateRevisionsInterval
     * @covers ::setValidateRevisionsInterval
     * @covers ::getValidateRevisionsTimestamp
     * @covers ::setValidateRevisionsTimestamp
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
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
        $repository->setRepositoryProperty((new RepositoryProperty())->setName('property')->setValue('value'));

        static::assertNull($repository->getRepositoryProperty('foobar'));
        static::assertSame('value', $repository->getRepositoryProperty('property'));
    }

    /**
     * @covers ::setRepositoryProperty
     * @covers ::getRepositoryProperties
     * @covers ::removeRepositoryProperty
     */
    public function testRepositoryPropertyAccessors(): void
    {
        $propertyA = new RepositoryProperty('property', '5');
        $propertyB = new RepositoryProperty('property', '10');

        $repository = new Repository();
        $repository->setRepositoryProperty($propertyA);
        static::assertSame([$propertyA], $repository->getRepositoryProperties()->getValues());

        $repository->setRepositoryProperty($propertyB);
        static::assertSame([$propertyA], $repository->getRepositoryProperties()->getValues());
        static::assertSame('10', $propertyA->getValue());

        $repository->removeRepositoryProperty($propertyA);
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
