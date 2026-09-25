<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Finder\SplFileInfo;

class SearchResult
{
    /** @var SearchResultLine[] */
    private array $lines = [];

    public function __construct(public readonly Repository $repository, public readonly SplFileInfo $file)
    {
    }

    public function addLine(SearchResultLine $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * @return SearchResultLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}
