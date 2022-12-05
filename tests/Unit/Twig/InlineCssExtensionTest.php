<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\InlineCss\CssToInlineStyles;
use DR\Review\Twig\InlineCssExtension;

/**
 * @coversDefaultClass \DR\Review\Twig\InlineCssExtension
 * @covers ::__construct
 */
class InlineCssExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new InlineCssExtension(new CssToInlineStyles());
        static::assertCount(1, $extension->getFilters());
    }

    /**
     * @covers ::inlineCss
     */
    public function testInlineCss(): void
    {
        $inliner = $this->createMock(CssToInlineStyles::class);
        $inliner->expects(static::once())->method('convert')->with('foobar', 'css')->willReturn('html');

        $extension = new InlineCssExtension($inliner);
        $result    = $extension->inlineCss('foobar', 'css');
        static::assertSame('html', $result);
    }
}
