<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Optimizer;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;

class DiffLineStateDeterminator
{
    /**
     * @param DiffChange[] $changes
     */
    public function determineState(array $changes): int
    {
        $types = [];
        foreach ($changes as $change) {
            $types[$change->type] = true;
        }

        if (count($types) === 0) {
            return DiffLine::STATE_UNCHANGED;
        }

        if (count($types) > 1) {
            return DiffLine::STATE_CHANGED;
        }

        return match (key($types)) {
            DiffChange::ADDED     => DiffLine::STATE_ADDED,
            DiffChange::REMOVED   => DiffLine::STATE_REMOVED,
            DiffChange::UNCHANGED => DiffLine::STATE_INLINED,
            default               => DiffLine::STATE_CHANGED,
        };
    }
}
