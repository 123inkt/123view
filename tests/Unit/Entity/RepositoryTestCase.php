<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Entity\RepositoryProperty;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Repository
 */
class RepositoryTestCase extends AbstractTestCase
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
