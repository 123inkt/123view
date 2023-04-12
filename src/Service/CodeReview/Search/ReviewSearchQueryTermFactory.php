<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\ParserHasFailed;

class ReviewSearchQueryTermFactory
{
    public function __construct(private readonly ReviewSearchQueryParserFactory $parserFactory)
    {
    }

    /**
     * @throws ParserHasFailed|Exception
     */
    public function getSearchTerms(string $searchQuery): TermInterface
    {
        if ($searchQuery === '') {
            return new EmptyMatch();
        }

        /** @var TermInterface $terms */
        $terms = $this->parserFactory->createParser()->tryString($searchQuery)->output();

        return $terms;
    }
}
