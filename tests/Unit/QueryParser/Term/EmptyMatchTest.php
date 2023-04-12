<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term;

use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmptyMatch::class)]
class EmptyMatchTest extends AbstractTestCase
{
    public function testToString(): void
    {
        static::assertSame('<empty>', (string)new EmptyMatch());
    }
}
