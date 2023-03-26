<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLineCollection
{
    /** @var DiffLine[] */
    private array $lines;

    /**
     * @param DiffLine[] $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    /**
     * @return DiffLine[]
     */
    public function toArray(): array
    {
        return array_values($this->lines);
    }

    /**
     * @return array<DiffLine|DiffLineChangeSet>
     */
    public function getDiffLineSet(): array
    {
        $result  = [];
        $removed = [];
        $added   = [];

        // gather all the added and removed pairs
        foreach ($this->lines as $line) {
            if ($line->state === DiffLine::STATE_REMOVED) {
                $removed[] = $line;
            } elseif ($line->state === DiffLine::STATE_ADDED) {
                $added[] = $line;
            } elseif ($line->state === DiffLine::STATE_UNCHANGED) {
                if (count($removed) > 0 || count($added) > 0) {
                    $result[] = new DiffLineChangeSet($removed, $added);
                    $added    = [];
                    $removed  = [];
                }
                $result[] = $line;
            }
        }

        if (count($removed) > 0 || count($added) > 0) {
            $result[] = new DiffLineChangeSet($removed, $added);
        }

        return $result;
    }
}
