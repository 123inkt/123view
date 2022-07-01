<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\Highlight;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use DR\GitCommitNotification\Twig\Highlight\PHPHighlighter;
use DR\GitCommitNotification\Twig\Highlight\TwigHighlighter;

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
        $factory = new HighlighterFactory();

        static::assertInstanceOf(PHPHighlighter::class, $factory->getHighlighter(PHPHighlighter::EXTENSION));
        static::assertInstanceOf(TwigHighlighter::class, $factory->getHighlighter(TwigHighlighter::EXTENSION));
        static::assertNull($factory->getHighlighter('foo'));
    }
}
