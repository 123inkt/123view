<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

class DiffLineChangeSet
{
    public const NEWLINE = "\n";

    /**
     * @param DiffLine[] $removed
     * @param DiffLine[] $added
     */
    public function __construct(public readonly array $removed, public readonly array $added)
    {
    }

    public function getLineNumbers(): ?DiffLineNumberPair
    {
        $lineNumberBefore = null;
        $lineNumberAfter  = null;

        foreach ($this->removed as $line) {
            $lineNumberBefore = $line->lineNumberBefore;
            break;
        }

        foreach ($this->added as $line) {
            $lineNumberAfter = $line->lineNumberAfter;
            break;
        }

        if ($lineNumberBefore === null || $lineNumberAfter === null) {
            return null;
        }

        return new DiffLineNumberPair($lineNumberBefore, $lineNumberAfter);
    }

    public function getTextBefore(): string
    {
        $text = '';
        foreach ($this->removed as $line) {
            $text .= $line->getLine() . self::NEWLINE;
        }

        return $text;
    }

    public function getTextAfter(): string
    {
        $text = '';
        foreach ($this->added as $line) {
            $text .= $line->getLine() . self::NEWLINE;
        }

        return $text;
    }

    public function setUnchanged(): void
    {
        foreach ($this->removed as $line) {
            $line->state           = DiffLine::STATE_UNCHANGED;
            $line->lineNumberAfter = $line->lineNumberBefore;
        }
        foreach ($this->added as $line) {
            $line->state            = DiffLine::STATE_UNCHANGED;
            $line->lineNumberBefore = $line->lineNumberAfter;
        }
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
