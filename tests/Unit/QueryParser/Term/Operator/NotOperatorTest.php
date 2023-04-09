<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser\Term\Operator;

use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\Operator\NotOperator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NotOperator::class)]
class NotOperatorTest extends AbstractTestCase
{
    public function testToString(): void
    {
        $operator = new NotOperator(new MatchWord('value'));
        static::assertSame('NOT ("value")', (string)$operator);
    }
}
