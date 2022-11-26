<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Notification;

use DR\GitCommitNotification\Entity\Notification\RuleOptions;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Notification\RuleOptions
 */
class RuleOptionsTest extends AbstractTestCase
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
