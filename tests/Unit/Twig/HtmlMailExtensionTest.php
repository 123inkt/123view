<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\HtmlMailExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(HtmlMailExtension::class)]
class HtmlMailExtensionTest extends AbstractTestCase
{
    #[DataProvider('dataProvider')]
    public function testConvert(string $html, string $expected): void
    {
        static::assertSame($expected, (new HtmlMailExtension())->convert($html));
    }

    /**
     * @return array<string[]>
     */
    public static function dataProvider(): array
    {
        return [
            ['<ul><li>foo</li></ul>', '&#9679; foo<br>'],
            ['<ul><li>foo</li><li>bar</li></ul>', '&#9679; foo<br>&#9679; bar<br>'],
        ];
    }
}
