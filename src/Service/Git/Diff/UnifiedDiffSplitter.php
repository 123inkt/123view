<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;

class UnifiedDiffSplitter
{
    public function splitFile(DiffFile $rightFile): DiffFile
    {
        $leftFile                 = new DiffFile();
        $leftFile->filePathBefore = $rightFile->filePathBefore;
        $leftFile->filePathAfter  = $rightFile->filePathAfter;
        $leftFile->fileModeBefore = $rightFile->fileModeBefore;
        $leftFile->fileModeAfter  = $rightFile->fileModeAfter;

        foreach ($rightFile->getBlocks() as $rightBlock) {
            $leftBlock = new DiffBlock();
            $leftFile->addBlock($leftBlock);
            $rightLines        = $rightBlock->lines;
            $rightBlock->lines = [];

            foreach ($rightLines as $rightLine) {
                if ($rightLine->state === DiffLine::STATE_ADDED) {
                    $rightBlock->lines[] = $rightLine;
                } elseif ($rightLine->state === DiffLine::STATE_REMOVED) {
                    $leftBlock->lines[] = $rightLine;
                } else {
                    $this->balanceLines($leftBlock, $rightBlock);
                    $rightBlock->lines[] = $rightLine;
                    $leftBlock->lines[]  = $rightLine;
                }
            }
            $this->balanceLines($leftBlock, $rightBlock);
        }

        return $leftFile;
    }

    private function balanceLines(DiffBlock $leftBlock, DiffBlock $rightBlock): void
    {
        $leftLines  = count($leftBlock->lines);
        $rightLines = count($rightBlock->lines);

        if ($leftLines === $rightLines) {
            return;
        }

        if ($leftLines < $rightLines) {
            for ($i = $rightLines - $leftLines; $i > 0; $i--) {
                $leftBlock->lines[] = new DiffLine(DiffLine::STATE_EMPTY, []);
            }
        } else {
            for ($i = $leftLines - $rightLines; $i > 0; $i--) {
                $rightBlock->lines[] = new DiffLine(DiffLine::STATE_EMPTY, []);
            }
        }
    }
}
