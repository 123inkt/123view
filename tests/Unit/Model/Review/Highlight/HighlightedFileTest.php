<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Review\Highlight;

use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Model\Review\Highlight\HighlightedFile
 * @covers ::__construct
 */
class HighlightedFileTest extends AbstractTestCase
{
    /**
     * @covers ::getLine
     */
    public function testGetLine(): void
    {
        $file = new HighlightedFile('filepath', [0 => 'line1', 2 => 'line2']);
        static::assertSame('line2', $file->getLine(3));
    }
}
