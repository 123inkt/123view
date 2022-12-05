<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeTokenizer;

class StringReader
{
    private string $string;
    private int    $cursor = 0;
    private int    $length;

    public function __construct(string $string)
    {
        $this->string = $string;
        $this->length = strlen($string);
    }

    public function current(): ?string
    {
        return $this->string[$this->cursor] ?? null;
    }

    public function next(): ?string
    {
        ++$this->cursor;

        return $this->string[$this->cursor] ?? null;
    }

    public function prev(): ?string
    {
        --$this->cursor;

        return $this->string[$this->cursor] ?? null;
    }

    public function peek(): ?string
    {
        return $this->string[$this->cursor + 1] ?? null;
    }

    public function eol(): bool
    {
        return $this->cursor >= $this->length;
    }
}
