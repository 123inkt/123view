<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLineChangeSet
{
    /**
     * @param DiffLine[] $removed
     * @param DiffLine[] $added
     */
    public function __construct(public readonly array $removed, public readonly array $added)
    {
    }

    public function getTextBefore(): string
    {
        $text = '';
        foreach ($this->removed as $line) {
            $text .= $line->getLine() . "\n";
        }

        return $text;
    }

    public function getTextAfter(): string
    {
        $text = '';
        foreach ($this->added as $line) {
            $text .= $line->getLine() . "\n";
        }

        return $text;
    }

    public function clearChanges(): void
    {
        foreach ($this->removed as $line) {
            $line->changes->clear();
        }
        foreach ($this->added as $line) {
            $line->changes->clear();
        }
    }
}
