<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\Model\QueryParser\AndOperator;
use DR\Review\Model\QueryParser\NotOperator;
use DR\Review\Model\QueryParser\OrOperator;
use DR\Review\Model\Review\QueryParser\MatchAuthor;
use DR\Review\Model\Review\QueryParser\MatchReviewer;
use DR\Review\Model\Review\QueryParser\MatchReviewId;
use DR\Review\Model\Review\QueryParser\MatchReviewState;
use DR\Review\Model\Review\QueryParser\SearchWord;
use Exception;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\Expression\binaryOperator;
use function Parsica\Parsica\Expression\expression;
use function Parsica\Parsica\Expression\leftAssoc;
use function Parsica\Parsica\Expression\prefix;
use function Parsica\Parsica\Expression\unaryOperator;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\many;
use function Parsica\Parsica\recursive;
use function Parsica\Parsica\satisfy;
use function Parsica\Parsica\skipHSpace;
use function Parsica\Parsica\some;
use function Parsica\Parsica\string;
use function Parsica\Parsica\stringI;

class ReviewSearchQueryParserFactory
{
    /**
     * @throws Exception
     */
    public function createParse(): Parser
    {
        // A term is a literal TRUE/FALSE or a variable
        $term = static fn(): Parser => self::tokens(
            choice(
                string('id:')->followedBy(atLeastOne(digitChar()))->map(static fn($value) => new MatchReviewId((int)$value)),
                string('state:')->followedBy(choice(stringI('open'), stringI('closed')))->map(static fn($value) => new MatchReviewState($value)),
                string('author:')->followedBy(self::stringLiteral())->map(static fn($value) => new MatchAuthor(implode('', $value))),
                string('reviewer:')->followedBy(self::stringLiteral())->map(static fn($value) => new MatchReviewer($value)),
                self::stringLiteral()->map(static fn($value) => new SearchWord($value)),
            )
        );

        $expr = recursive();

        // When the parser encounters NOT, AND, or OR, it returns a Not_, And_, or Or_ object.
        // The $v, $l and $r arguments can be Boolean objects themselves, creating the tree.
        $expr->recurse(
            expression(
                self::parens($expr)->or($term()),
                [
                    prefix(
                        unaryOperator(self::tokens(stringI("NOT")), static fn($v) => new NotOperator($v))
                    ),
                    leftAssoc(
                        binaryOperator(self::tokens(stringI("AND")), static fn($l, $r) => new AndOperator($l, $r))
                    ),
                    leftAssoc(
                        binaryOperator(self::tokens(stringI("OR")), static fn($l, $r) => new OrOperator($l, $r))
                    )
                ]
            )
        );

        return $expr->thenEof(); // check if we reached the end of the input
    }

    private static function tokens(Parser $parser): Parser
    {
        return keepFirst($parser, skipHSpace());
    }

    private static function parens(Parser $parser): Parser
    {
        return self::tokens(between(self::tokens(char('(')), self::tokens(char(')')), $parser));
    }

    private static function stringLiteral(): Parser
    {
        return choice(
            between(
                char('"'),
                char('"'),
                some(
                    choice(
                        satisfy(static fn(string $char): bool => in_array($char, ['"', '\\'], true) === false),
                        char("\\")->followedBy(
                            choice(
                                char("\"")->map(static fn() => '"'),
                                char("\\")->map(static fn() => '\\'),
                            )
                        )
                    )
                )
            ),
            many(satisfy(static fn(string $x): bool => in_array($x, [" ", "\t", "(", ")"], true) === false))
        );
    }
}
