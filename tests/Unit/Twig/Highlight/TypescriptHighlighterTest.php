<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\TypescriptHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\TypescriptHighlighter
 */
class TypescriptHighlighterTest extends AbstractTestCase
{
    /**
     * @covers ::highlight
     * @dataProvider keywordDataProvider
     */
    public function testHighlightBlockOpeners(string $code): void
    {
        $highlighter = new TypescriptHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{' . $code . '}}', $result);
    }

    /**
     * @return array<string, string[]>
     */
    public function keywordDataProvider(): array
    {
        return [
            ['break'],
            ['boolean'],
            ['case'],
            ['catch'],
            ['class'],
            ['const'],
            ['continue'],
            ['debugger'],
            ['default'],
            ['delete'],
            ['describe'],
            ['do'],
            ['else'],
            ['enum'],
            ['export'],
            ['extends'],
            ['false'],
            ['finally'],
            ['for'],
            ['function'],
            ['from'],
            ['if'],
            ['import'],
            ['in'],
            ['instanceof'],
            ['new'],
            ['null'],
            ['number'],
            ['return'],
            ['super'],
            ['switch'],
            ['string'],
            ['this'],
            ['throw'],
            ['true'],
            ['try'],
            ['typeof'],
            ['object'],
            ['var'],
            ['void'],
            ['while'],
            ['with'],
        ];
    }
}
