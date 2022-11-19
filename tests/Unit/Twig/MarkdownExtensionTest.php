<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\MarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\MarkdownExtension
 * @covers ::__construct
 */
class MarkdownExtensionTest extends AbstractTestCase
{
    private MarkdownConverter&MockObject $converter;
    private MarkdownExtension            $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->converter = $this->createMock(MarkdownConverter::class);
        $this->extension = new MarkdownExtension($this->converter);
    }

    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        static::assertCount(1, $this->extension->getFilters());
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

        static::assertSame($expected, $this->extension->convert($string));
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
