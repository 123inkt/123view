<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\InlineCss\CssToInlineStyles;
use DR\Review\Twig\InlineCssExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InlineCssExtension::class)]
class InlineCssExtensionTest extends AbstractTestCase
{
    public function testGetFilters(): void
    {
        $extension = new InlineCssExtension(new CssToInlineStyles());
        static::assertCount(1, $extension->getFilters());
    }

    public function testInlineCss(): void
    {
        $inliner = $this->createMock(CssToInlineStyles::class);
        $inliner->expects($this->once())->method('convert')->with('foobar', 'css')->willReturn('html');

        $extension = new InlineCssExtension($inliner);
        $result    = $extension->inlineCss('foobar', 'css');
        static::assertSame('html', $result);
    }
}
