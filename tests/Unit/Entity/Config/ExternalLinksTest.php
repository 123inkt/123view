<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\ExternalLinks;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\ExternalLinks
 */
class ExternalLinksTest extends AbstractTest
{
    /**
     * @covers ::getExternalLinks
     * @covers ::addExternalLink
     */
    public function testGetExternalLinks(): void
    {
        $links = new ExternalLinks();
        static::assertEmpty($links->getExternalLinks());

        $link = new ExternalLink();
        $links->addExternalLink($link);
        static::assertSame([$link], $links->getExternalLinks());
    }
}
