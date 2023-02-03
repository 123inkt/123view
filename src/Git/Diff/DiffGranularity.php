<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

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
                Delimiters::PARAGRAPH,
                Delimiters::SENTENCE,
                Delimiters::WORD,
                Delimiters::CHARACTER,
            ]
        );
    }
}
