<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\Highlight;

use Closure;

class HighlightedFile
{
    /** @var array<int, string>|null */
    private ?array $lines = null;

    /**
     * @param Closure(): array<int, string> $closure
     */
    public function __construct(public readonly string $filePath, public readonly Closure $closure)
    {
    }

    public function getLine(int $lineNumber): ?string
    {
        if ($this->lines === null) {
            $this->lines = ($this->closure)();
        }

        return $this->lines[$lineNumber - 1] ?? null;
    }
}
