<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeCommentTokenizer;
use DR\GitCommitNotification\Service\CodeTokenizer\CodeStringTokenizer;
use DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\HighlightPattern;
use DR\GitCommitNotification\Twig\Highlight\PHPHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\PHPHighlighter
 */
class PHPHighlighterTest extends AbstractTestCase
{
    private PHPHighlighter $highlighter;

    public function setUp(): void
    {
        parent::setUp();
        $pattern           = new HighlightPattern('[[%s]]', '((%s))', '{{%s}}');
        $tokenizer         = new CodeTokenizer(new CodeStringTokenizer(), new CodeCommentTokenizer());
        $this->highlighter = new PHPHighlighter($pattern, $tokenizer);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightSkipEmptyString(): void
    {
        static::assertSame('', $this->highlighter->highlight('', "{{", "}}"));
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightFunction(): void
    {
        $code = 'function test(bool $value = true) {';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{function}} test({{bool}} $value = {{true}}) {', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightScalars(): void
    {
        $code = 'true false bool float int array callable string null';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{true}} {{false}} {{bool}} {{float}} {{int}} {{array}} {{callable}} {{string}} {{null}}', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightControlKeywords(): void
    {
        $code = 'continue break return switch for throw';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{continue}} {{break}} {{return}} {{switch}} {{for}} {{throw}}', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightClassKeywords(): void
    {
        $code = 'abstract class interface final extends instanceof implements';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('{{abstract}} {{class}} {{interface}} {{final}} {{extends}} {{instanceof}} {{implements}}', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightString(): void
    {
        $code = 'foo "bar"';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('foo ((&quot;bar&quot;))', $result);
    }

    /**
     * @covers ::highlight
     */
    public function testHighlightComment(): void
    {
        $code = 'foo // comment';

        $result = $this->highlighter->highlight($code, "{{", "}}");
        static::assertSame('foo [[// comment]]', $result);
    }
}
