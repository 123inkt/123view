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
     * @return Parser<TermInterface>
     */
    public static function tokens(Parser $parser): Parser
    {
        return keepFirst($parser, skipHSpace());
    }

    /**
     * @return Parser<TermInterface>
     */
    public static function parens(Parser $parser): Parser
    {
        return self::tokens(between(self::tokens(char('(')), self::tokens(char(')')), $parser));
    }

    /**
     * @param callable(): Parser<TermInterface> $term
     *
     * @return Parser<TermInterface>
     * @throws Exception
     */
    public static function recursiveExpression(callable $term): Parser
    {
        $expr = recursive();

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
                    leftAssoc(binaryOperator(ParserFactory::tokens(stringI("AND")), static fn($left, $right) => new AndOperator($left, $right))),
                    leftAssoc(binaryOperator(ParserFactory::tokens(stringI("OR")), static fn($left, $right) => new OrOperator($left, $right)))
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
        return choice(self::quotedString(), self::expressionString());
    }

    /**
     * @return Parser<string|string[]>
     */
    public static function expressionString(): Parser
    {
        return atLeastOne(satisfy(static fn(string $x): bool => in_array($x, [" ", "\t", "(", ")"], true) === false));
    }

    /**
     * @return Parser<string|string[]>
     */
    public static function quotedString(): Parser
    {
        return between(
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
            )
        );
    }
}
