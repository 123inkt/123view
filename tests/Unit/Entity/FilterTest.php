<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DR\GitCommitNotification\Entity\Filter;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Filter
 */
class FilterTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Filter())->getId());
        static::assertAccessorPairs(Filter::class);
    }
}
