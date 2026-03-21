<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\LineReference;
use DR\Utils\Assert;

class DiffFinder
{
    public function __construct(private readonly LineReferenceMatcher $referenceMatcher)
    {
    }

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
            $filePath = $matches[1];
            $hashEnd  = $matches[2];
        }

        // filepath matched, but not the hash
        $partiallyMatchedFile = null;
        $renameMatchedFile    = null;

        foreach ($files as $file) {
            if ($file->getPathname() === $filePath) {
                $partiallyMatchedFile = $file;

                if ($hashEnd === null || $file->hashEnd === $hashEnd) {
                    return $file;
                }
            }
            if ($file->isRename() && $file->filePathBefore === $filePath) {
                $renameMatchedFile = $file;
            }
        }

        return $partiallyMatchedFile ?? $renameMatchedFile;
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

            $index = Assert::integer(array_search($line, $block->lines, true));

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
        return $this->referenceMatcher->exactMatch($lines, $lineReference)
            ?? $this->referenceMatcher->bestEffortMatch($lines, $lineReference);
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
