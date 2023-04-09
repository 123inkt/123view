<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term\Match;

use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MatchWord::class)]
class MatchWordTest extends AbstractTestCase
{
    public function testToString(): void
    {
        static::assertSame('"value"', (string)new MatchWord('value'));
        static::assertSame('"value"', (string)new MatchWord(['val', 'ue']));
    }
}
