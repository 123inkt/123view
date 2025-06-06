<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class DiffAlgorithmType extends AbstractEnumType
{
    public const PATIENCE  = 'patience';
    public const MINIMAL   = 'minimal';
    public const HISTOGRAM = 'histogram';
    public const MYERS     = 'myers';

    public const string   TYPE   = 'enum_diff_algorithm';
    public const array    VALUES = [self::PATIENCE, self::MINIMAL, self::HISTOGRAM, self::MYERS];
}
