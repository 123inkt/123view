<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Search;

use DR\Review\Model\Search\SearchResultCollection;

readonly class SearchCodeViewModel
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(public SearchResultCollection $searchResults, public string $searchQuery, public ?string $fileExtension)
    {
    }
}
