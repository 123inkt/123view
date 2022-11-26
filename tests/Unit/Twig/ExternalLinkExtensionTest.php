<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\ExternalLinkExtension;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\ExternalLinkExtension
 * @covers ::__construct
 */
class ExternalLinkExtensionTest extends AbstractTestCase
{
    private ExternalLinkRepository&MockObject $linkRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
    }

    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new ExternalLinkExtension($this->linkRepository);
        $filters   = $extension->getFilters();

        static::assertCount(1, $filters);

        $filter = $filters[0];
        static::assertSame('external_links', $filter->getName());
    }

    /**
     * @covers ::getLinks
     * @covers ::injectExternalLinks
     */
    public function testInjectExternalLinks(): void
    {
        $html = 'A commit message for JB1234 ticket.';
        $link = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit message for <a href="https://mycompany.com/jira/1234" class="external-link" target="_blank">JB1234</a> ticket.';

        $this->linkRepository->expects(self::once())->method('findAll')->willReturn([$link]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($html);
        static::assertSame($expect, $actual);
    }

    /**
     * @covers ::getLinks
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
        $expect = 'F#123 <a href="https://mycompany.com/jira/456" class="external-link" target="_blank">US#456</a> ';
        $expect .= '<a href="https://mycompany.com/jira/789" class="external-link" target="_blank">T#789</a> A random task';

        $this->linkRepository->expects(self::once())->method('findAll')->willReturn([$linkA, $linkB]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($html);
        static::assertSame($expect, $actual);
    }

    /**
     * @covers ::getLinks
     * @covers ::injectExternalLinks
     */
    public function testInjectExternalLinksMultipleOccurrences(): void
    {
        $html = 'A commit message for JB1234 and JB4567 ticket.';
        $link = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit message for <a href="https://mycompany.com/jira/1234" class="external-link" target="_blank">JB1234</a> and ';
        $expect .= '<a href="https://mycompany.com/jira/4567" class="external-link" target="_blank">JB4567</a> ticket.';

        $this->linkRepository->expects(self::once())->method('findAll')->willReturn([$link]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($html);
        static::assertSame($expect, $actual);
    }
}
