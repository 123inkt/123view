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
        foreach ($lines as $line) {
            if ($line->lineNumberAfter !== null && $line->lineNumberAfter === $lineReference->lineAfter) {
                return $line;
            }

            if ($line->lineNumberBefore !== null && $line->lineNumberBefore === $lineReference->lineBefore) {
                return $line;
            }
        }

        return null;
    }
}
