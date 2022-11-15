<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\XmlHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\XmlHighlighter
 */
class XmlHighlighterTest extends AbstractTestCase
{
    /**
     * @covers ::highlight
     */
    public function testHighlight(): void
    {
        $highlighter = new XmlHighlighter();

        $result = $highlighter->highlight('<xml tag="test"></xml>', '[[', ']]');
        static::assertSame('&lt;xml [[tag]]=&quot;test&quot;&gt;&lt;/xml&gt;', $result);
    }
}
