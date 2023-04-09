<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use Exception;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\ParserHasFailed;

class ReviewSearchQueryParser
{
    private ?Parser $parser = null;

    public function __construct(private readonly ReviewSearchQueryParserFactory $parserFactory)
    {
    }

    /**
     * @throws ParserHasFailed|Exception
     */
    public function parse(string $searchQuery): ParseResult
    {
        $this->parser ??= $this->parserFactory->createParser();

        return $this->parser->tryString($searchQuery);
    }
}
