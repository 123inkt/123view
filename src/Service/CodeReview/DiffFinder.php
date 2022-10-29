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

        $hashEnd = null;
        if (preg_match('/^(.*):(\w+)$/', $filePath, $matches) === 1) {
            $filePath = (string)$matches[1];
            $hashEnd  = (string)$matches[2];
        }

        foreach ($files as $file) {
            if ($hashEnd !== null && $file->hashEnd !== $hashEnd) {
                continue;
            }

            if ($file->getFile()?->getPathname() !== $filePath) {
                continue;
            }

            return $file;
        }

        return null;
    }

    public function findLineInFile(DiffFile $file, LineReference $lineReference): ?DiffLine
    {
        foreach ($file->getBlocks() as $block) {
            if ($file->isAdded()) {
                $line = $this->findLineInNewFile($block->lines, $lineReference);
            } else {
                $line = $this->findLineInLines($block->lines, $lineReference);
            }
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

    /**
     * @param DiffLine[] $lines
     */
    public function findLineInNewFile(array $lines, LineReference $lineReference): ?DiffLine
    {
        $lineNumber = $lineReference->lineAfter - 1;

        return $lines[$lineNumber] ?? null;
    }
}
