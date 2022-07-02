<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\ExternalLink
 */
class ExternalLinkTest extends AbstractTestCase
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
