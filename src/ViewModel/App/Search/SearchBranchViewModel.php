<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Search;

readonly class SearchBranchViewModel
{
    /**
     * @codeCoverageIgnore  Simple DTO
     *
     * @param string[] $branches
     */
    public function __construct(public array $branches, public string $searchQuery)
    {
    }
}
