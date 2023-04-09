<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AndOperator::class)]
class AndOperatorTest extends AbstractTestCase
{
    public function testToString(): void
    {
        $operator = new AndOperator(new MatchWord('left'), new MatchWord('right'));
        static::assertSame('("left") AND ("right")', (string)$operator);
    }

    public function testCreateRequiresAtLeastTwoArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least two terms are required to create an AND operator');
        AndOperator::create(new MatchWord('value'));
    }

    public function testCreate(): void
    {
        $operator = AndOperator::create(new MatchWord('left'), new MatchWord('middle'), new MatchWord('right'));
        static::assertSame('("left") AND (("middle") AND ("right"))', (string)$operator);
    }
}
