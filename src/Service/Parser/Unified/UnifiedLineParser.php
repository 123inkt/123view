<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Parser\Unified;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
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

        switch ($line[0]) {
            case '-':
                return new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, $code)]);
            case '+':
                return new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, $code)]);
            case ' ':
                return new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, $code)]);
            case '\\':
                return null;
            default:
                throw new LogicException(sprintf('Invalid unified patch character `%s` of line `%s`.', $line[0], $line));
        }
    }
}
