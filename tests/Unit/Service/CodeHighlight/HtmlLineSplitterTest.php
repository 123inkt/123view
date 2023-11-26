<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeHighlight;

use DR\Review\Service\CodeHighlight\HighlightHtmlLineSplitter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightHtmlLineSplitter::class)]
class HtmlLineSplitterTest extends AbstractTestCase
{
    public function testSplitStringWithoutNewline(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(["<span>foobar</span>"], $splitter->split("<span>foobar</span>"));
    }

    public function testSplitStringWithSingleNewLine(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(["<span>foo</span>", "<span>bar</span>"], $splitter->split("<span>foo\nbar</span>"));
    }

    public function testSplitStringWithNestedTags(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(
            ["<span><span>foo</span></span>", "<span><span>bar</span>foo</span>", "<span>bar</span>"],
            $splitter->split("<span><span>foo\nbar</span>foo\nbar</span>")
        );
    }

    public function testSplitStringWithMultipleNewlines(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(
            [
                '<span><span>foo</span></span>',
                '',
                '<span><span>foo</span></span>',
                '<span><span>bar</span></span>',
            ],
            $splitter->split("<span><span>foo\n\nfoo\nbar</span></span>")
        );
    }
}
