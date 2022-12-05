<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLine
{
    public const STATE_ADDED     = 1;
    public const STATE_REMOVED   = 2;
    public const STATE_CHANGED   = 3;
    public const STATE_UNCHANGED = 4;

    public ?int                 $lineNumberBefore = null;
    public ?int                 $lineNumberAfter  = null;
    public int                  $state;
    public DiffChangeCollection $changes;

    /**
     * @param DiffChange[] $changes
     */
    public function __construct(int $state, array $changes)
    {
        $this->state   = $state;
        $this->changes = new DiffChangeCollection($changes);
    }

    /**
     * @param int[] $exclude
     */
    public function getLine(?array $exclude = null): string
    {
        $result = [];
        foreach ($this->changes->toArray() as $change) {
            if ($exclude === null || in_array($change->type, $exclude, true) === false) {
                $result[] = $change->code;
            }
        }

        return implode('', $result);
    }
}
