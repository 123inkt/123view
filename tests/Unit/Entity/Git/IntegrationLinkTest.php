<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git;

use DR\Review\Entity\Git\IntegrationLink;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\IntegrationLink
 * @covers ::__construct
 */
class IntegrationLinkTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $link = new IntegrationLink('url', 'image', 'text');
        static::assertSame('url', $link->url);
        static::assertSame('image', $link->image);
        static::assertSame('text', $link->text);
    }
}
