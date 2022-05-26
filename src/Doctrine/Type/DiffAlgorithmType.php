<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

class DiffAlgorithmType extends AbstractEnumType
{
    public const PATIENCE  = 'patience';
    public const MINIMAL   = 'minimal';
    public const HISTOGRAM = 'histogram';
    public const MYERS     = 'myers';

    protected const TYPE   = 'enum_diff_algorithm';
    protected const VALUES = [self::PATIENCE, self::MINIMAL, self::HISTOGRAM, self::MYERS];
}
