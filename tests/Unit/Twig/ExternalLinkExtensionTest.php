<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\ExternalLinkExtension;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\ExternalLinkExtension
 */
class ExternalLinkExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new ExternalLinkExtension();
        $filters   = $extension->getFilters();

        static::assertCount(1, $filters);

        $filter = $filters[0];
        static::assertSame('external_links', $filter->getName());
    }

    /**
     * @covers ::injectExternalLinks
     */
    public function testInjectExternalLinks(): void
    {
        $html = 'A commit message for JB1234 ticket.';
        $link = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit message for <a href="https://mycompany.com/jira/1234" class="external-link">JB1234</a> ticket.';

        $extension = new ExternalLinkExtension();
        $actual    = $extension->injectExternalLinks($html, [$link]);
        static::assertSame($expect, $actual);
    }

    /**
     * @covers ::injectExternalLinks
     */
    public function testInjectExternalLinksMultipleUrls(): void
    {
        $html  = 'F#123 US#456 T#789 A random task';
        $linkA = new ExternalLink();
        $linkA->setPattern('US#{}');
        $linkA->setUrl('https://mycompany.com/jira/{}');
        $linkB = new ExternalLink();
        $linkB->setPattern('T#{}');
        $linkB->setUrl('https://mycompany.com/jira/{}');
        $expect = 'F#123 <a href="https://mycompany.com/jira/456" class="external-link">US#456</a> ';
        $expect .= '<a href="https://mycompany.com/jira/789" class="external-link">T#789</a> A random task';

        $extension = new ExternalLinkExtension();
        $actual    = $extension->injectExternalLinks($html, [$linkA, $linkB]);
        static::assertSame($expect, $actual);
    }

    /**
     * @covers ::injectExternalLinks
     */
    public function testInjectExternalLinksMultipleOccurrences(): void
    {
        $html = 'A commit message for JB1234 and JB4567 ticket.';
        $link = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit message for <a href="https://mycompany.com/jira/1234" class="external-link">JB1234</a> and ';
        $expect .= '<a href="https://mycompany.com/jira/4567" class="external-link">JB4567</a> ticket.';

        $extension = new ExternalLinkExtension();
        $actual    = $extension->injectExternalLinks($html, [$link]);
        static::assertSame($expect, $actual);
    }
}
