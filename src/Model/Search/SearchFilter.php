<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

readonly class SearchFilter
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(public string $searchQuery, public ?string $filename, public bool $regexEnabled)
    {
    }
}
