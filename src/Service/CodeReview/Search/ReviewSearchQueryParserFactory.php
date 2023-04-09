<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\QueryParser\ParserFactory;
use DR\Review\QueryParser\Term\Match\MatchFilter;
use DR\Review\QueryParser\Term\Match\MatchWord;
use Exception;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\string;
use function Parsica\Parsica\stringI;

class ReviewSearchQueryParserFactory
{
    /**
     * @throws Exception
     */
    public function createParse(): Parser
    {
        // build query parser either with prefix [id, state, author, reviewer] or without prefix
        $term = static fn(): Parser => ParserFactory::tokens(
            choice(
                string('id:')->followedBy(atLeastOne(digitChar()))->map(static fn($val) => new MatchFilter('id', $val)),
                string('state:')->followedBy(choice(stringI('open'), stringI('closed')))->map(static fn($val) => new MatchFilter('state', $val)),
                string('author:')->followedBy(ParserFactory::stringLiteral())->map(static fn($val) => new MatchFilter('author', $val)),
                string('reviewer:')->followedBy(ParserFactory::stringLiteral())->map(static fn($val) => new MatchFilter('reviewer', $val)),
                ParserFactory::stringLiteral()->map(static fn($val) => new MatchWord($val)),
            )
        );

        return ParserFactory::recursiveExpression($term)->thenEof();
    }
}
