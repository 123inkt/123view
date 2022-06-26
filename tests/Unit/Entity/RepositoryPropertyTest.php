<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DR\GitCommitNotification\Entity\RepositoryProperty;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\RepositoryProperty
 */
class RepositoryPropertyTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RepositoryProperty::class);
    }
}
