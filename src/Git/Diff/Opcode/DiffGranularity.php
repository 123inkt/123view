<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff\Opcode;

use cogpowered\FineDiff\Delimiters;
use cogpowered\FineDiff\Granularity\Granularity;

class DiffGranularity extends Granularity
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->setDelimiters(
            [
                Delimiters::WORD,
                ['/', '\\', '-', '+', '=', '>', '<', ';', ':', '(', ')', '{', '}', '[', ']', '|', '!', '?', ',', ' ', '.', "\t"],
            ]
        );
    }
}
