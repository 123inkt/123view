<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
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
        $config->setExcludedMethods(['addRepositoryProperty', 'getRepositoryProperties']);

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
}
