<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

use Generator;

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

    public function remove(DiffLine $line): self
    {
        $index = array_search($line, $this->lines, true);
        if ($index !== false) {
            unset($this->lines[$index]);
        }

        return $this;
    }

    /**
     * @return DiffLine[]
     */
    public function toArray(): array
    {
        return array_values($this->lines);
    }

    /**
     * @return Generator<array<DiffLinePair>>
     */
    public function getChangePairs(): Generator
    {
        $set     = 0;
        $removed = [];
        $added   = [];

        // gather all the added and removed pairs
        foreach ($this->lines as $line) {
            if ($line->state === DiffLine::STATE_REMOVED) {
                $removed[$set][] = $line;
            } elseif ($line->state === DiffLine::STATE_ADDED) {
                $added[$set][] = $line;
            } elseif ($line->state === DiffLine::STATE_UNCHANGED) {
                ++$set;
            }
        }
        // iterate over the set
        for ($s = 0; $s <= $set; $s++) {
            if (isset($removed[$s], $added[$s]) === false) {
                continue;
            }

            $max = max(count($removed[$s]), count($added[$s]));

            // iterate over the set and let it yield
            $pairs = [];
            for ($i = 0; $i < $max; $i++) {
                if (isset($removed[$s][$i], $added[$s][$i])) {
                    $pairs[] = new DiffLinePair($removed[$s][$i], $added[$s][$i]);
                }
            }

            yield $pairs;
        }
    }
}
