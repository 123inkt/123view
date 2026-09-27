<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

readonly class SearchFilter
{
    /**
     * @codeCoverageIgnore Simple DTO
     *
     * @param non-empty-array<string>|null $extensions
     */
    public function __construct(public string $searchQuery, public ?array $extensions, public bool $regexEnabled)
    {
    }
}
