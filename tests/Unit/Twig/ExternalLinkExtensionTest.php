<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Repository\Config\ExternalLinkRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\ExternalLinkExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ExternalLinkExtension::class)]
class ExternalLinkExtensionTest extends AbstractTestCase
{
    private ExternalLinkRepository&MockObject $linkRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
    }

    public function testInjectExternalLinks(): void
    {
        $content = 'A commit <message> for JB1234 ticket.';
        $link    = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit &lt;message&gt; for <a href="https://mycompany.com/jira/1234" class="external-link" target="_blank">JB1234</a> ticket.';

        $this->linkRepository->expects($this->once())->method('findAll')->willReturn([$link]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($content);
        static::assertSame($expect, $actual);
    }

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

        $this->linkRepository->expects($this->once())->method('findAll')->willReturn([$linkA, $linkB]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($html);
        static::assertSame($expect, $actual);
    }

    public function testInjectExternalLinksMultipleOccurrences(): void
    {
        $html = 'A commit message for JB1234 and JB4567 ticket.';
        $link = new ExternalLink();
        $link->setPattern('JB{}');
        $link->setUrl('https://mycompany.com/jira/{}');
        $expect = 'A commit message for <a href="https://mycompany.com/jira/1234" class="external-link" target="_blank">JB1234</a> and ';
        $expect .= '<a href="https://mycompany.com/jira/4567" class="external-link" target="_blank">JB4567</a> ticket.';

        $this->linkRepository->expects($this->once())->method('findAll')->willReturn([$link]);

        $extension = new ExternalLinkExtension($this->linkRepository);
        $actual    = $extension->injectExternalLinks($html);
        static::assertSame($expect, $actual);
    }
}
