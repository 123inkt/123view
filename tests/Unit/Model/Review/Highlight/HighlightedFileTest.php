<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Review\Highlight;

use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightedFile::class)]
class HighlightedFileTest extends AbstractTestCase
{
    public function testGetLine(): void
    {
        $file = new HighlightedFile('filepath', static fn() => [0 => 'line1', 2 => 'line2']);
        static::assertSame('line2', $file->getLine(3));
    }
}
