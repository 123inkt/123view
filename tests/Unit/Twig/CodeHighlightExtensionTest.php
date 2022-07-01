<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\CodeHighlightExtension;
use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use DR\GitCommitNotification\Twig\Highlight\HighlighterInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\CodeHighlightExtension
 * @covers ::__construct
 */
class CodeHighlightExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new CodeHighlightExtension($this->createMock(HighlighterFactory::class));
        static::assertCount(1, $extension->getFilters());
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightKnownLanguage(): void
    {
        $highlighter = $this->createMock(HighlighterInterface::class);
        $highlighter->expects(static::once())->method('highlight')->with('foobar', '<div class="class">', '</div>')->willReturn('html');

        $factory = $this->createMock(HighlighterFactory::class);
        $factory->expects(static::once())->method('getHighlighter')->with('php')->willReturn($highlighter);

        $extension = new CodeHighlightExtension($factory);
        static::assertSame('html', $extension->highlight('foobar', 'php', 'class', 'div'));
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightUnknownLanguage(): void
    {
        $factory = $this->createMock(HighlighterFactory::class);
        $factory->expects(static::once())->method('getHighlighter')->with('foo')->willReturn(null);

        $extension = new CodeHighlightExtension($factory);
        static::assertSame('foobar', $extension->highlight('foobar', 'foo', 'class'));
    }
}
