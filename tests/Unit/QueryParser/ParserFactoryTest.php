<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser;

use DR\Review\QueryParser\ParserFactory;
use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\QueryParser\Term\Operator\NotOperator;
use DR\Review\QueryParser\Term\Operator\OrOperator;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParserHasFailed;
use PHPUnit\Framework\Attributes\CoversClass;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\digitChar;

#[CoversClass(ParserFactory::class)]
class ParserFactoryTest extends AbstractTestCase
{
    /**
     * @throws ParserHasFailed
     */
    public function testTokens(): void
    {
        $parser = ParserFactory::tokens(atLeastOne(digitChar()));
        static::assertSame('123', $parser->tryString('123  ')->output());
    }

    /**
     * @throws ParserHasFailed
     */
    public function testParens(): void
    {
        $parser = ParserFactory::parens(atLeastOne(digitChar()));
        static::assertSame('123', $parser->tryString('(123)')->output());
    }

    /**
     * @throws ParserHasFailed
     */
    public function testStringLiteral(): void
    {
        $parser = ParserFactory::stringLiteral();
        static::assertSame('abc', $parser->tryString('abc')->output());
        static::assertSame('abc', $parser->tryString('"abc"')->output());
    }

    /**
     * @throws ParserHasFailed
     */
    public function testExpressionString(): void
    {
        $parser = ParserFactory::expressionString();
        static::assertSame('abc', $parser->tryString('abc ')->output());
        static::assertSame('abc', $parser->tryString('abc( ')->output());
        static::assertSame('abc', $parser->tryString('abc) ')->output());
    }

    /**
     * @throws ParserHasFailed
     */
    public function testQuotedString(): void
    {
        $parser = ParserFactory::quotedString();
        static::assertSame('abc', $parser->tryString('"abc"')->output());
        static::assertSame('a"bc', $parser->tryString('"a\"bc"')->output());
        static::assertSame('abc\\', $parser->tryString('"abc\\\\"')->output());
    }

    /**
     * @throws ParserHasFailed
     * @throws Exception
     */
    public function testRecursiveExpression(): void
    {
        $terms  = static fn(): Parser => ParserFactory::tokens(ParserFactory::stringLiteral()->map(static fn($val) => new MatchWord($val)));
        $parser = ParserFactory::recursiveExpression($terms);

        static::assertEquals(new NotOperator(new MatchWord('abc')), $parser->tryString('not abc')->output());
        static::assertEquals(new AndOperator(new MatchWord('abc'), new MatchWord('efg')), $parser->tryString('abc and efg')->output());
        static::assertEquals(new OrOperator(new MatchWord('abc'), new MatchWord('efg')), $parser->tryString('abc or efg')->output());
    }
}
