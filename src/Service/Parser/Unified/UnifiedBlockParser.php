<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser\Unified;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\LineReader;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;

class UnifiedBlockParser
{
    private UnifiedLineParser   $lineParser;
    private ?UnifiedDiffBundler $bundler;

    public function __construct(UnifiedLineParser $lineParser, ?UnifiedDiffBundler $bundler)
    {
        $this->lineParser = $lineParser;
        $this->bundler    = $bundler;
    }

    public function parse(int $startBefore, int $startAfter, LineReader $reader): DiffBlock
    {
        $block = new DiffBlock();

        for ($line = $reader->current(); $line !== null; $line = $reader->next()) {
            if ($line === '') {
                continue;
            }

            $diffLine = $this->lineParser->parse($line);
            if ($diffLine === null) {
                continue;
            }

            // update line number
            if (in_array($diffLine->state, [DiffLine::STATE_REMOVED, DiffLine::STATE_CHANGED, DiffLine::STATE_UNCHANGED], true)) {
                $diffLine->lineNumberBefore = $startBefore;
                ++$startBefore;
            }
            if (in_array($diffLine->state, [DiffLine::STATE_ADDED, DiffLine::STATE_CHANGED, DiffLine::STATE_UNCHANGED], true)) {
                $diffLine->lineNumberAfter = $startAfter;
                ++$startAfter;
            }

            $block->lines[] = $diffLine;
        }

        if ($this->bundler !== null) {
            $block->lines = $this->bundler->bundle($block->lines);
        }

        return $block;
    }
}
