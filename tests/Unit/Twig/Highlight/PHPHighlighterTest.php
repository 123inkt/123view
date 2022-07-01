<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\PHPHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\PHPHighlighter
 */
class PHPHighlighterTest extends AbstractTestCase
{
    /**
     * @covers ::highlight
     */
    public function testHighlightFunction(): void
    {
        $code = 'function test(bool $value = true) {';

        $highlighter = new PHPHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{function}} test({{bool}} $value = {{true}}) {', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightScalars(): void
    {
        $code = 'true false bool float int array callable string null';

        $highlighter = new PHPHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{true}} {{false}} {{bool}} {{float}} {{int}} {{array}} {{callable}} {{string}} {{null}}', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightControlKeywords(): void
    {
        $code = 'continue break return switch for throw';

        $highlighter = new PHPHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{continue}} {{break}} {{return}} {{switch}} {{for}} {{throw}}', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightClassKeywords(): void
    {
        $code = 'abstract class interface final extends instanceof implements';

        $highlighter = new PHPHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{abstract}} {{class}} {{interface}} {{final}} {{extends}} {{instanceof}} {{implements}}', $result);
    }
}
