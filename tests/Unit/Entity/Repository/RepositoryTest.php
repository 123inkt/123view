<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Repository::class)]
class RepositoryTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = new ConstraintConfig();
        $config->setExcludedMethods(['addRepositoryProperty', 'getRepositoryProperties', 'getReviews', 'getRevisions']);

        static::assertNull((new Repository())->getId());
        static::assertAccessorPairs(Repository::class, $config);
    }

    public function testUrlAccessor(): void
    {
        $repository = new Repository();
        static::assertFalse($repository->hasUrl());

        $repository->setUrl(Uri::new('https://example.com'));
        static::assertSame('https://example.com', (string)$repository->getUrl());
    }

    public function testGetRepositoryProperty(): void
    {
        $repository = new Repository();
        $repository->setRepositoryProperty((new RepositoryProperty('foo', 'bar'))->setName('property')->setValue('value'));

        static::assertNull($repository->getRepositoryProperty('foobar'));
        static::assertSame('value', $repository->getRepositoryProperty('property'));
    }

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

    public function testRevisions(): void
    {
        $collection = new ArrayCollection();

        $repository = new Repository();
        static::assertInstanceOf(ArrayCollection::class, $repository->getRevisions());

        $repository->setRevisions($collection);
        static::assertSame($collection, $repository->getRevisions());
    }

    public function testReviews(): void
    {
        $collection = new ArrayCollection();

        $repository = new Repository();
        static::assertInstanceOf(ArrayCollection::class, $repository->getReviews());

        $repository->setReviews($collection);
        static::assertSame($collection, $repository->getReviews());
    }

    public function testEqualsTo(): void
    {
        $repositoryA = (new Repository())->setId(123);
        $repositoryB = (new Repository())->setId(456);

        static::assertTrue($repositoryA->equalsTo($repositoryA));
        static::assertFalse($repositoryA->equalsTo($repositoryB));
        static::assertFalse($repositoryA->equalsTo(new stdClass()));
    }
}
