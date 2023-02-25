<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Config;

use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Config\ExternalLink
 */
class ExternalLinkTest extends AbstractTestCase
{
    /**
     * @covers ::getId
     * @covers ::setUrl
     * @covers ::getUrl
     * @covers ::setPattern
     * @covers ::getPattern
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new ExternalLink())->getId());
        static::assertAccessorPairs(ExternalLink::class);
    }
}
