<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\LineReference;

class DiffFinder
{
    /**
     * @param DiffFile[] $files
     */
    public function findFileByPath(array $files, ?string $filePath): ?DiffFile
    {
        if ($filePath === null) {
            return null;
        }

        foreach ($files as $file) {
            if ($file->getFile()?->getPathname() === $filePath) {
                return $file;
            }
        }

        return null;
    }

    public function findLineInFile(DiffFile $file, LineReference $lineReference): ?DiffLine
    {
        foreach ($file->blocks as $block) {
            $line = $this->findLineInLines($block->lines, $lineReference);
            if ($line !== null) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @param DiffLine[] $lines
     */
    public function findLineInLines(array $lines, LineReference $lineReference): ?DiffLine
    {
        foreach ($lines as $index => $line) {
            if ($line->lineNumberBefore !== $lineReference->line) {
                continue;
            }

            // find the next line with the correct offset. Must have empty lineNumberBefore
            $lineMatch = $lines[$index + $lineReference->offset] ?? null;
            if ($lineMatch === null) {
                return $line;
            }

            if ($lineMatch->lineNumberAfter === null || $lineMatch->lineNumberAfter === $lineReference->lineAfter) {
                return $lineMatch;
            }

            return $line;
        }

        return null;
    }
}
