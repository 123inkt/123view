<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\QueryParser;

use DR\Review\Model\QueryParser\TermInterface;

class MatchReviewState implements TermInterface
{
    public function __construct(public readonly string $state)
    {
    }
}
