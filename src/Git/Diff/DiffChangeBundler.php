<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

use cogpowered\FineDiff\Diff;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Git\Diff\Opcode\DiffChangeFactory;
use DR\Review\Git\Diff\Opcode\DiffChangeOptimizer;

/**
 * Use cogpowered\FineDiff to calculate the word diff for a string without the common prefix and suffix
 */
class DiffChangeBundler
{
    public function __construct(
        private readonly Diff $diff,
        private readonly DiffChangeFactory $changeFactory,
        private readonly DiffChangeOptimizer $changeOptimizer
    ) {
    }

    public function bundle(DiffChange $changeBefore, DiffChange $changeAfter): DiffChangeCollection
    {
        $opcodes = $this->diff->getOpcodes($changeBefore->code, $changeAfter->code)->generate();
        $changes = $this->changeFactory->createFromOpcodes($changeBefore->code, $opcodes);

        return $this->changeOptimizer->optimize($changes);
    }
}
