<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Markdown;

use DR\Review\Service\Markdown\MarkdownService;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Markdown\MarkdownService
 * @covers ::__construct
 */
class MarkdownServiceTest extends AbstractTestCase
{
    private MarkdownConverter&MockObject $converter;
    private MarkdownService              $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->converter = $this->createMock(MarkdownConverter::class);
        $this->service   = new MarkdownService($this->converter);
    }

    /**
     * @dataProvider dataProvider
     * @covers ::convert
     */
    public function testConvert(string $markdown, string $expected): void
    {
        $string = 'string';

        $content = $this->createMock(RenderedContentInterface::class);
        $content->expects(self::once())->method('getContent')->willReturn($markdown);

        $this->converter->expects(self::once())->method('convert')->with($string)->willReturn($content);

        static::assertSame($expected, $this->service->convert($string));
    }

    /**
     * @return array<string[]>
     */
    public function dataProvider(): array
    {
        return [
            ["foo\nbar", "foo<br>\nbar"],
            ["<span>foo bar</span>", '<span>foo bar</span>'],
            ["<span>foo</span>\nbar", "<span>foo</span>\nbar"],
        ];
    }
}
