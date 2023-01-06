<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

class ActivityVariable
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly string $key, public readonly string $value, public readonly bool $htmlSafe = false)
    {
    }
}
