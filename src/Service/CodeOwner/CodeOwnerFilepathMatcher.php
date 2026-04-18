<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;

readonly class CodeOwnerFilepathMatcher
{
    public function __construct(private CodeOwnerPatternMatcher $matcher)
    {
    }

    /**
     * @param array<OwnerPattern> $patterns
     */
    public function match(string $filename, array $patterns): ?OwnerPattern
    {
        return array_find($patterns, fn(OwnerPattern $pattern): bool => $this->matcher->match($filename, $pattern));
    }
}
