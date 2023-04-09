<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term\Match;

use DR\Review\QueryParser\Term\Match\MatchFilter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MatchFilter::class)]
class MatchFilterTest extends AbstractTestCase
{
    public function testToString(): void
    {
        static::assertSame('prefix:"value"', (string)new MatchFilter('prefix', 'value'));
        static::assertSame('prefix:"value"', (string)new MatchFilter('prefix', ['val', 'ue']));
    }
}
