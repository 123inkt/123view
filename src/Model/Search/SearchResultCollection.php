<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

readonly class SearchResultCollection
{
    /**
     * @codeCoverageIgnore Simple DTO
     * @param SearchResult[] $results
     */
    public function __construct(public array $results, public bool $moreResultsAvailable)
    {
    }
}
