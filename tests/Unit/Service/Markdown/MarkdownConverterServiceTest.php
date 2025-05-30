<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Markdown;

use DR\Review\Service\Markdown\MarkdownConverterService;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function PHPUnit\Framework\once;

#[CoversClass(MarkdownConverterService::class)]
class MarkdownConverterServiceTest extends AbstractTestCase
{
    private MarkdownConverter&MockObject $markdownConverter;
    private MarkdownConverterService     $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->markdownConverter = $this->createMock(MarkdownConverter::class);
        $this->service           = new MarkdownConverterService($this->markdownConverter);
    }

    /**
     * @throws CommonMarkException
     */
    public function testConvert(): void
    {
        $string = 'string';

        $renderedContent = $this->createMock(RenderedContentInterface::class);
        $renderedContent->expects(once())->method('getContent')->willReturn("markdown: " . $string);

        $this->markdownConverter->expects($this->once())->method('convert')->with($string)->willReturn($renderedContent);

        static::assertSame("markdown: string", $this->service->convert($string));
    }
}
