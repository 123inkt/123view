<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Match;

class MatchFilter
{
    public readonly string $author;

    /**
     * @param string|string[] $value
     */
    public function __construct(public readonly string $prefix, string|array $value)
    {
        $this->author = is_array($value) ? implode('', $value) : $value;
    }
}
