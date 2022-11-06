<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use DR\GitCommitNotification\Twig\Highlight\PHPHighlighter;
use DR\GitCommitNotification\Twig\Highlight\TwigHighlighter;
use DR\GitCommitNotification\Twig\Highlight\TypescriptHighlighter;
use DR\GitCommitNotification\Twig\Highlight\XmlHighlighter;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\Highlight\HighlighterFactory
 */
class HighlighterFactoryTest extends AbstractTestCase
{
    /**
     * @covers ::getHighlighter
     */
    public function testGetHighlighter(): void
    {
        $factory = new HighlighterFactory($this->createMock(CodeTokenizer::class));

        static::assertInstanceOf(PHPHighlighter::class, $factory->getHighlighter(PHPHighlighter::EXTENSION));
        static::assertInstanceOf(TwigHighlighter::class, $factory->getHighlighter(TwigHighlighter::EXTENSION));
        static::assertInstanceOf(TypescriptHighlighter::class, $factory->getHighlighter(TypescriptHighlighter::EXTENSION));
        static::assertInstanceOf(XmlHighlighter::class, $factory->getHighlighter(XmlHighlighter::EXTENSION));
        static::assertNull($factory->getHighlighter('foo'));
    }
}
