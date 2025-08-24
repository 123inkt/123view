<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

readonly class SearchResultCollection
{
    /**
     * @codeCoverageIgnore Simple DTO
     *
     * @param SearchResult[] $results
     */
    public function __construct(public array $results, public bool $moreResultsAvailable)
    {
    }

    /**
     * @return iterable<int, SearchResult[]>
     */
    public function iteratePerRepository(): iterable
    {
        $grouped = [];
        foreach ($this->results as $result) {
            $repoId             = (int)$result->repository->getId();
            $grouped[$repoId]   ??= [];
            $grouped[$repoId][] = $result;
        }
        foreach ($grouped as $repoId => $results) {
            yield $repoId => $results;
        }
    }
}
