<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Config;

use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExternalLink::class)]
class ExternalLinkTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertFalse((new ExternalLink())->hasId());
        static::assertAccessorPairs(ExternalLink::class);
    }
}
