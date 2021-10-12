<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git;

use DR\GitCommitNotification\Entity\Git\IntegrationLink;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\IntegrationLink
 * @covers ::__construct
 */
class IntegrationLinkTest extends AbstractTest
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
