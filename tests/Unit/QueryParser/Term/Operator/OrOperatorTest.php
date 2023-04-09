<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\Operator\OrOperator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OrOperator::class)]
class OrOperatorTest extends AbstractTestCase
{
    public function testToString(): void
    {
        $operator = new OrOperator(new MatchWord('left'), new MatchWord('right'));
        static::assertSame('("left") OR ("right")', (string)$operator);
    }
}
