<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\TwigHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\TwigHighlighter
 */
class TwigHighlighterTest extends AbstractTestCase
{
    /**
     * @covers ::highlight
     */
    public function testHighlightBlockOpeners(): void
    {
        $code     = 'apply autoescape block cache embed if for flush macro sandbox verbatim embed with';
        $expected = '{{apply}} {{autoescape}} {{block}} {{cache}} {{embed}} ';
        $expected .= '{{if}} {{for}} {{flush }}{{macro}} {{sandbox}} {{verbatim}} {{embed}} {{with}}';

        $highlighter = new TwigHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame($expected, $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightBlockClosers(): void
    {
        $code     = 'endapply endautoescape endblock endcache endembed endif endfor endflush endmacro endsandbox endverbatim endembed endwith';
        $expected = '{{endapply}} {{endautoescape}} {{endblock}} {{endcache}} {{endembed}} {{endif}} ';
        $expected .= '{{endfor}} {{endflush}} {{endmacro}} {{endsandbox}} {{endverbatim}} {{endembed}} {{endwith}}';

        $highlighter = new TwigHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame($expected, $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightControlStructures(): void
    {
        $code     = 'elseif else and or set use include import do from macro';
        $expected = '{{elseif}} {{else}} {{and}} {{or}} {{set}} {{use}} {{include}} {{import}} {{do}} {{from}} {{macro}}';

        $highlighter = new TwigHighlighter();
        $result      = $highlighter->highlight($code, "{{", "}}");
        static::assertSame($expected, $result);
    }
}
