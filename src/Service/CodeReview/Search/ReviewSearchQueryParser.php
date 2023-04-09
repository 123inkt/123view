<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use DR\Review\QueryParser\Term\TermInterface;
use Exception;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\ParserHasFailed;

class ReviewSearchQueryParser
{
    /** @var Parser<TermInterface>|null */
    private ?Parser $parser = null;

    public function __construct(private readonly ReviewSearchQueryParserFactory $parserFactory)
    {
    }

    /**
     * @return ParseResult<TermInterface>
     * @throws ParserHasFailed|Exception
     */
    public function parse(string $searchQuery): ParseResult
    {
        $this->parser ??= $this->parserFactory->createParser();

        return $this->parser->tryString($searchQuery);
    }
}
