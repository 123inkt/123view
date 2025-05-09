<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

readonly class SearchResultLine
{
    public function __construct(public string $line, public int $lineNumber, public SearchResultLineTypeEnum $type)
    {
    }
}
