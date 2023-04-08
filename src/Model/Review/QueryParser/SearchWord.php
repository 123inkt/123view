<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\QueryParser;

use DR\Review\Model\QueryParser\TermInterface;

class SearchWord implements TermInterface
{
    public readonly string $query;

    /**
     * @param string|string[] $query
     */
    public function __construct(string|array $query)
    {
        $this->query = is_array($query) ? implode('', $query) : $query;
    }
}
