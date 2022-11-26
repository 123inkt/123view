<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Repository;

use DR\GitCommitNotification\Entity\Repository\RepositoryProperty;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Repository\RepositoryProperty
 */
class RepositoryPropertyTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RepositoryProperty::class);
    }
}
