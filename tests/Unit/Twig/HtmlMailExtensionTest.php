<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\HtmlMailExtension;

/**
 * @coversDefaultClass \DR\Review\Twig\HtmlMailExtension
 */
class HtmlMailExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        $extension = new HtmlMailExtension();
        $filters   = $extension->getFilters();

        static::assertCount(1, $filters);

        $filter = $filters[0];
        static::assertSame('html_mail', $filter->getName());
    }

    /**
     * @dataProvider  dataProvider
     * @covers ::convert
     */
    public function testConvert(string $html, string $expected): void
    {
        static::assertSame($expected, (new HtmlMailExtension())->convert($html));
    }

    /**
     * @return array<string[]>
     */
    public function dataProvider(): array
    {
        return [
            ['<ul><li>foo</li></ul>', '&#9679; foo<br>'],
            ['<ul><li>foo</li><li>bar</li></ul>', '&#9679; foo<br>&#9679; bar<br>'],
        ];
    }
}
