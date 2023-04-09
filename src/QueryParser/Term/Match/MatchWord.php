<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Match;

use DR\Review\QueryParser\Term\TermInterface;

class MatchWord implements TermInterface
{
    public readonly string $query;

    /**
     * @param string|string[] $query
     */
    public function __construct(string|array $query)
    {
        $this->query = is_array($query) ? implode('', $query) : $query;
    }

    public function __toString(): string
    {
        return '"' . $this->query . '"';
    }
}
