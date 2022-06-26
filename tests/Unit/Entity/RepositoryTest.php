<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Repository
 */
class RepositoryTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Repository())->getId());
        static::assertAccessorPairs(Repository::class);
    }
}
