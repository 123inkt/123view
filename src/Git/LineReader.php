<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Git;

class LineReader
{
    private int $cursor = 0;
    /** @var string[] */
    private array $lines;

    /**
     * @param string[] $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public static function fromString(string $string): LineReader
    {
        return new LineReader(explode("\n", $string));
    }

    public function current(): ?string
    {
        return $this->lines[$this->cursor] ?? null;
    }

    public function next(): ?string
    {
        ++$this->cursor;

        return $this->lines[$this->cursor] ?? null;
    }

    /**
     * Peek at the next line
     */
    public function peek(): ?string
    {
        return $this->lines[$this->cursor + 1] ?? null;
    }

    public function __toString(): string
    {
        return implode("\n", $this->lines);
    }
}
