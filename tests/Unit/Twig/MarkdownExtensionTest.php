<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Service\Markdown\MarkdownService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\MarkdownExtension;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\MarkdownExtension
 * @covers ::__construct
 */
class MarkdownExtensionTest extends AbstractTestCase
{
    private MarkdownService&MockObject $markdownService;
    private MarkdownExtension          $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->markdownService = $this->createMock(MarkdownService::class);
        $this->extension       = new MarkdownExtension($this->markdownService);
    }

    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        static::assertCount(1, $this->extension->getFilters());
    }

    /**
     * @covers ::convert
     */
    public function testConvert(): void
    {
        $string = 'string';

        $this->markdownService->expects(self::once())->method('convert')->with($string)->willReturn("markdown: " . $string);

        static::assertSame("markdown: string", $this->extension->convert($string));
    }
}
