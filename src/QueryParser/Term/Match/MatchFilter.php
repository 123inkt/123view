<?php
declare(strict_types=1);

namespace DR\Review\QueryParser\Term\Match;

use DR\Review\QueryParser\Term\TermInterface;

class MatchFilter implements TermInterface
{
    public readonly string $value;

    /**
     * @param string|string[] $value
     */
    public function __construct(public readonly string $prefix, string|array $value)
    {
        $this->value = is_array($value) ? implode('', $value) : $value;
    }

    public function __toString(): string
    {
        return $this->prefix . ':"' . $this->value . '"';
    }
}
