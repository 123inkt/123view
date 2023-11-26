<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Service\Markdown\MarkdownConverterService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\MarkdownExtension;
use League\CommonMark\Exception\CommonMarkException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Twig\MarkdownExtension
 * @covers ::__construct
 */
class MarkdownExtensionTest extends AbstractTestCase
{
    private MarkdownConverterService&MockObject $markdownConverter;
    private MarkdownExtension                   $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->markdownConverter = $this->createMock(MarkdownConverterService::class);
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
     * @throws CommonMarkException
     */
    public function testConvert(): void
    {
        $this->markdownConverter->expects(self::once())->method('convert')->with('string')->willReturn("markdown: string");

        static::assertSame("markdown: string", $this->extension->convert('string'));
    }
}
