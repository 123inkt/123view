<?php
declare(strict_types=1);

namespace DR\Review\QueryParser;

use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\QueryParser\Term\Operator\NotOperator;
use DR\Review\QueryParser\Term\Operator\OrOperator;
use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\Expression\binaryOperator;
use function Parsica\Parsica\Expression\expression;
use function Parsica\Parsica\Expression\leftAssoc;
use function Parsica\Parsica\Expression\prefix;
use function Parsica\Parsica\Expression\unaryOperator;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\recursive;
use function Parsica\Parsica\satisfy;
use function Parsica\Parsica\skipHSpace;
use function Parsica\Parsica\some;
use function Parsica\Parsica\stringI;

class ParserFactory
{
    /**
     * @template T
     * @param Parser<T> $parser
     *
     * @return Parser<T>
     */
    public static function tokens(Parser $parser): Parser
    {
        /** @var Parser<T> $resultParser */
        $resultParser = keepFirst($parser, skipHSpace());

        return $resultParser;
    }

    /**
     * @template T
     * @param Parser<T> $parser
     *
     * @return Parser<T>
     */
    public static function parens(Parser $parser): Parser
    {
        /** @var Parser<T> $resultParser */
        $resultParser = self::tokens(between(self::tokens(char('(')), self::tokens(char(')')), $parser));

        return $resultParser;
    }

    /**
     * @template T
     * @param callable(): Parser<T> $term
     *
     * @return Parser<T>
     * @throws Exception
     */
    public static function recursiveExpression(callable $term): Parser
    {
        /** @var Parser<T> $expr */
        $expr = recursive();

        $andCallable = static fn(TermInterface $left, TermInterface $right) => new AndOperator($left, $right);
        $orCallable  = static fn(TermInterface $left, TermInterface $right) => new OrOperator($left, $right);

        // When the parser encounters NOT, AND, or OR, it returns a NotOperator, AndOperator, or OrOperator object.
        // The $val, $left and $right arguments can be TermInterface objects themselves, creating the tree.
        $expr->recurse(
            expression(
            // either a term or a parenthesized expression
                self::parens($expr)->or($term()),
                [
                    // The NOT operator is a prefix operator, so it only has one argument.
                    prefix(unaryOperator(ParserFactory::tokens(stringI("NOT")), static fn($val) => new NotOperator($val))),
                    // The AND and OR operators are infix operators, so they have two arguments.
                    leftAssoc(binaryOperator(ParserFactory::tokens(stringI("AND")), $andCallable)),
                    leftAssoc(binaryOperator(ParserFactory::tokens(stringI("OR")), $orCallable))
                ]
            )
        );

        return $expr;
    }

    /**
     * @return Parser<string|string[]>
     */
    public static function stringLiteral(): Parser
    {
        /** @var Parser<string|string[]> $parser */
        $parser = choice(self::quotedString(), self::expressionString());

        return $parser;
    }

    /**
     * @return Parser<string|string[]>
     */
    public static function expressionString(): Parser
    {
        /** @var Parser<string|string[]> $parser */
        $parser = atLeastOne(satisfy(static fn(string $x): bool => in_array($x, [" ", "\t", "(", ")"], true) === false));

        return $parser;
    }

    /**
     * @return Parser<string>
     */
    public static function quotedString(): Parser
    {
        /** @var Parser<string> $parser */
        $parser = between(
            char('"'),
            char('"'),
            some(
                choice(
                    satisfy(static fn(string $char): bool => in_array($char, ['"', '\\'], true) === false),
                    char("\\")
                        ->followedBy(
                            choice(
                                char("\"")->map(static fn() => '"'),
                                char("\\")->map(static fn() => '\\'),
                            )
                        )
                )
            )->map(static fn(array $chars) => implode('', $chars))
        );

        return $parser;
    }
}
