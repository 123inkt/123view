<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DR\GitCommitNotification\Entity\RuleOptions;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\RuleOptions
 */
class RuleOptionsTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new RuleOptions())->getId());
        static::assertAccessorPairs(RuleOptions::class);
    }
}
