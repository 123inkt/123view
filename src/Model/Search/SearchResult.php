<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

use Symfony\Component\Finder\SplFileInfo;

class SearchResult
{
    public function __construct(public readonly SplFileInfo $file)
    {
    }

    /** @var SearchResultLine[] */
    public array $lines = [];
}
