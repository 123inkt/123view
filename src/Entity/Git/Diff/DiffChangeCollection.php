<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;

/**
 * @implements IteratorAggregate<DiffChange>
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DiffChangeCollection implements Countable, IteratorAggregate
{
    /** @var DiffChange[] */
    private array $changes;

    /**
     * @param DiffChange[] $changes
     */
    public function __construct(array $changes = [])
    {
        $this->changes = $changes;
    }

    public function get(int $index): DiffChange
    {
        if (isset($this->changes[$index]) === false) {
            throw new InvalidArgumentException('Invalid index in DiffChangeCollection: ' . $index);
        }

        return $this->changes[$index];
    }

    public function getOrNull(int $index): ?DiffChange
    {
        return $this->changes[$index] ?? null;
    }

    public function add(?DiffChange ...$changes): void
    {
        foreach ($changes as $change) {
            if ($change === null || $change->code === '') {
                continue;
            }

            // if last change is the same as the adding change, just concat
            $tail = $this->lastOrNull();
            if ($tail !== null && $tail->type === $change->type) {
                $tail->code .= $change->code;
                continue;
            }

            $this->changes[] = $change;
        }
    }

    public function first(): DiffChange
    {
        if (isset($this->changes[0]) === false) {
            throw new LogicException('Collection is empty, unable to get first');
        }

        return $this->changes[0];
    }

    public function firstOrNull(): ?DiffChange
    {
        if (count($this->changes) === 0) {
            return null;
        }

        return reset($this->changes);
    }

    public function lastOrNull(): ?DiffChange
    {
        return count($this->changes) === 0 ? null : end($this->changes);
    }

    public function clear(): void
    {
        $this->changes = [];
    }

    public function bundle(): self
    {
        //$result = [];
        //
        //for ($i = 0, $length = count($this->changes); $i < $length; $i++) {
        //    $current  = $this->changes[$i];
        //    $next     = $this->changes[$i + 1] ?? null;
        //    $nextNext = $this->changes[$i + 2] ?? null;
        //    $result[] = $current;
        //
        //    // check granularity. if there is a sequence Added-Unchanged-Added (or removed) and unchanged is letters/numbers only, merge the
        //    // sequence together as a single change
        //    if ($next === null
        //        || $nextNext === null
        //        || $next->type !== DiffChange::UNCHANGED
        //        || $current->type !== $nextNext->type
        //        || preg_match('/^[a-zA-Z0-9]+$/', $next->code) !== 1) {
        //        continue;
        //    }
        //
        //    $current->append($next, $nextNext);
        //    $i += 2;
        //}
        //
        //$this->changes = $result;

        return $this;
    }

    /**
     * @return DiffChange[]
     */
    public function toArray(): array
    {
        return $this->changes;
    }

    public function count(): int
    {
        return count($this->changes);
    }

    /**
     * @return ArrayIterator<int, DiffChange>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->changes);
    }
}
