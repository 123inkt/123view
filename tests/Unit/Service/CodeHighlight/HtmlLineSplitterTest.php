<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Service\CodeHighlight\HighlightHtmlLineSplitter;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeHighlight\HighlightHtmlLineSplitter
 */
class HtmlLineSplitterTest extends AbstractTestCase
{
    /**
     * @covers ::split
     */
    public function testSplitStringWithoutNewline(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(["<span>foobar</span>"], $splitter->split("<span>foobar</span>"));
    }

    /**
     * @covers ::split
     */
    public function testSplitStringWithSingleNewLine(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(["<span>foo</span>", "<span>bar</span>"], $splitter->split("<span>foo\nbar</span>"));
    }

    /**
     * @covers ::split
     */
    public function testSplitStringWithNestedTags(): void
    {
        $splitter = new HighlightHtmlLineSplitter();

        static::assertSame(
            ["<span><span>foo</span></span>", "<span><span>bar</span>foo</span>", "<span>bar</span>"],
            $splitter->split("<span><span>foo\nbar</span>foo\nbar</span>")
        );
    }

    /**
     * @covers ::split
     */
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
