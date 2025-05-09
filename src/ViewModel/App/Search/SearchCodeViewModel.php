<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Search;

use DR\Review\Model\Search\SearchResult;

readonly class SearchCodeViewModel
{
    /**
     * @param SearchResult[] $files
     */
    public function __construct(public array $files, public string $searchQuery)
    {
    }
}
