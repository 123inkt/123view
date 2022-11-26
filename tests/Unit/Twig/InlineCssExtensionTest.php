<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles;
use DR\GitCommitNotification\Twig\InlineCssExtension;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\InlineCssExtension
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
        $result = $extension->inlineCss('foobar', 'css');
        static::assertSame('html', $result);
    }
}
