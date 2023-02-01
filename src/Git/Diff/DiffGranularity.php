<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

use cogpowered\FineDiff\Delimiters;
use cogpowered\FineDiff\Granularity\Granularity;

class DiffGranularity extends Granularity
{
    /**
     * @var array<int, array<int, string>>
     */
    protected $delimiters = [
        Delimiters::WORD,
        [' ', ',', ';', '.', '?', '$'],
        [' ', '/', '=', '-', '>', '$'],
        ['[', ']', '(', ')', '{', '}']
    ];
}
