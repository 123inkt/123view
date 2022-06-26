<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity;

use DR\GitCommitNotification\Entity\ExternalLink;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\ExternalLink
 */
class ExternalLinkTest extends AbstractTest
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new ExternalLink())->getId());
        static::assertAccessorPairs(ExternalLink::class);
    }
}
