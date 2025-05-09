<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Finder\SplFileInfo;

class SearchResult
{
    public function __construct(
        public readonly Repository $repository,
        public readonly SplFileInfo $file
    ) {
    }

    /** @var SearchResultLine[] */
    public array $lines = [];
}
