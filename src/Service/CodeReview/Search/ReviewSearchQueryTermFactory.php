<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\ParserHasFailed;
use Parsica\Parsica\StringStream;

class ReviewSearchQueryTermFactory
{
    public function __construct(private readonly ReviewSearchQueryParserFactory $parserFactory)
    {
    }

    /**
     * @throws InvalidQueryException|Exception
     */
    public function getSearchTerms(string $searchQuery): TermInterface
    {
        if ($searchQuery === '') {
            return new EmptyMatch();
        }

        // too many final classes
        // @codeCoverageIgnoreStart
        $result = $this->parserFactory->createParser()->run(new StringStream($searchQuery));
        if ($result->isFail()) {
            /** @var Fail<TermInterface> $result */ // @phpcs:ignore
            throw new InvalidQueryException(new ParserHasFailed($result));
        }

        /** @var TermInterface $terms */
        $terms = $result->output();

        return $terms;
        // @codeCoverageIgnoreEnd
    }
}
