<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\QueryParser\ParserFactory;
use DR\Review\QueryParser\Term\Match\MatchFilter;
use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\Operator\AndOperator;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Utility\Assert;
use Exception;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\either;
use function Parsica\Parsica\string;
use function Parsica\Parsica\stringI;

class ReviewSearchQueryParserFactory
{
    /**
     * @return Parser<TermInterface>
     * @throws Exception
     */
    public function createParser(): Parser
    {
        // build query parser with boolean operators
        $term = static fn(): Parser => ParserFactory::tokens(self::terms());
        $expr = ParserFactory::recursiveExpression($term)->thenEof();

        // ---------------------------------------------------------
        // build query parser without boolean operators and will default to AND ... AND ... AND
        $term  = ParserFactory::tokens(self::terms()->map(static fn($val) => [$val]));
        $terms = atLeastOne($term)->map(static fn($val) => count($val) <= 1 ? $val : AndOperator::create(...$val))->thenEof();

        // ---------------------------------------------------------
        // first try expression parser, if that fails try terms parser
        /** @var Parser<TermInterface> $parser */
        $parser = either($expr, $terms);

        return $parser;
    }

    /**
     * Create term parser with prefix [id, state, author, reviewer] or without prefix
     * @return Parser<TermInterface>
     */
    private static function terms(): Parser
    {
        /** @var Parser<TermInterface> $parser */
        $parser = choice(
            string('id:')
                ->followedBy(atLeastOne(digitChar()))->map(static fn(string $val) => new MatchFilter('id', $val)),
            string('state:')
                ->followedBy(choice(stringI('open'), stringI('closed')))->map(static fn($val) => new MatchFilter('state', Assert::isString($val))),
            string('author:')
                ->followedBy(ParserFactory::stringLiteral())->map(static fn($val) => new MatchFilter('author', $val)),
            string('reviewer:')
                ->followedBy(ParserFactory::stringLiteral())->map(static fn($val) => new MatchFilter('reviewer', $val)),
            ParserFactory::stringLiteral()->map(static fn($val) => new MatchWord($val))->label("'search word'"),
        );

        return $parser;
    }
}
