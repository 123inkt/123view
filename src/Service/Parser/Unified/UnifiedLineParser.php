<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser\Unified;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use InvalidArgumentException;
use LogicException;

class UnifiedLineParser
{
    public function parse(string $line): ?DiffLine
    {
        if ($line === '') {
            throw new InvalidArgumentException('Line should be at least length 1 or greater');
        }

        $code = substr($line, 1);

        return match ($line[0]) {
            '-'     => new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, $code)]),
            '+'     => new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, $code)]),
            ' '     => new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, $code)]),
            '\\'    => null,
            default => throw new LogicException(sprintf('Invalid unified patch character `%s` of line `%s`.', $line[0], $line)),
        };
    }
}
