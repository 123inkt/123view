<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\QueryParser;

use DR\Review\Model\QueryParser\TermInterface;

class MatchReviewer implements TermInterface
{
    public readonly string $reviewer;

    /**
     * @param string|string[] $reviewer
     */
    public function __construct(string|array $reviewer)
    {
        $this->reviewer = is_array($reviewer) ? implode('', $reviewer) : $reviewer;
    }
}
