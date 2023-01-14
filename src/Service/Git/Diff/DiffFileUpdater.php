<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Review\FileDiffOptions;

class DiffFileUpdater
{
    /**
     * @param DiffFile[] $files
     *
     * @return  DiffFile[]
     */
    public function update(array $files, ?FileDiffOptions $options): array
    {
        if ($options === null || $options->visibleDiffLines === null) {
            return $files;
        }

        foreach ($files as $file) {
            $blocks = $file->getBlocks();
            $file->removeBlocks();

            foreach ($blocks as $block) {
                $this->updateBlockVisibility($block, $options->visibleDiffLines);

                if ($options->maxInvisibleLines !== null && $file->getTotalNrOfLines() >= $options->maxInvisibleLines) {
                    $file->addBlocks($this->removeInvisibleLines($block));
                } else {
                    $file->addBlock($block);
                }
            }
        }

        return $files;
    }

    private function updateBlockVisibility(DiffBlock $block, int $visibleDiffLines): void
    {
        $changedLineNr = null;

        foreach ($block->lines as $index => $line) {
            $line->visible = false;

            // line is visible if close enough to previously changed line
            if ($line->state === DiffLine::STATE_UNCHANGED) {
                if ($changedLineNr !== null && $index - $changedLineNr < $visibleDiffLines) {
                    $line->visible = true;
                }
                continue;
            }

            // modified line
            $changedLineNr = $index;
            $line->visible = true;

            // mark all previously unchanged lines as visible
            for ($i = 1; $i < $visibleDiffLines; $i++) {
                $prevLine = $block->lines[$index - $i] ?? null;
                if ($prevLine?->state !== DiffLine::STATE_UNCHANGED) {
                    break;
                }
                $prevLine->visible = true;
            }
        }
    }

    /**
     * @return DiffBlock[]
     */
    private function removeInvisibleLines(DiffBlock $block): array
    {
        $blocks       = [];
        $currentBlock = null;

        foreach ($block->lines as $line) {
            if ($line->visible === false) {
                $currentBlock = null;
                continue;
            }

            if ($currentBlock === null) {
                $blocks[] = $currentBlock = new DiffBlock();
            }
            $currentBlock->lines[] = $line;
        }

        return $blocks;
    }
}
