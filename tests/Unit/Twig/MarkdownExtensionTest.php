<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\MarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use function PHPUnit\Framework\once;

/**
 * @coversDefaultClass \DR\Review\Twig\MarkdownExtension
 * @covers ::__construct
 */
class MarkdownExtensionTest extends AbstractTestCase
{
    private MarkdownConverter&MockObject $markdownConverter;
    private MarkdownExtension            $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->markdownConverter = $this->createMock(MarkdownConverter::class);
        $this->extension         = new MarkdownExtension($this->markdownConverter);
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

        $renderedContent = $this->createMock(RenderedContentInterface::class);
        $renderedContent->expects(once())->method('getContent')->willReturn("markdown: " . $string);

        $this->markdownConverter->expects(self::once())->method('convert')->with($string)->willReturn($renderedContent);

        static::assertSame("markdown: string", $this->extension->convert($string));
    }
}
