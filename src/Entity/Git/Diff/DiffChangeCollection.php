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
