<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Utility\Assert;

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

    /**
     * @return array{before: DiffLine[], after: DiffLine[]}|null
     */
    public function findLinesAround(DiffFile $file, LineReference $lineReference, int $margin): ?array
    {
        foreach ($file->getBlocks() as $block) {
            $line = $this->findLineInBlock($file, $block, $lineReference);
            if ($line === null) {
                continue;
            }

            $index = Assert::isInt(array_search($line, $block->lines, true));

            $start = max(0, $index - $margin + 1);
            $lines = array_slice($block->lines, $start, $margin * 2);

            return [
                'before' => array_slice($lines, 0, $index - $start + 1),
                'after'  => array_slice($lines, $index - $start + 1, $margin)
            ];
        }

        return null;
    }

    public function findLineInFile(DiffFile $file, LineReference $lineReference): ?DiffLine
    {
        foreach ($file->getBlocks() as $block) {
            $line = $this->findLineInBlock($file, $block, $lineReference);
            if ($line !== null) {
                return $line;
            }
        }

        return null;
    }

    public function findLineInBlock(DiffFile $file, DiffBlock $block, LineReference $lineReference): ?DiffLine
    {
        if ($file->isAdded()) {
            return $this->findLineInNewFile($block->lines, $lineReference);
        }

        return $this->findLineInLines($block->lines, $lineReference);
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
