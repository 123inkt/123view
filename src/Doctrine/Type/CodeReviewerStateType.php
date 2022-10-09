<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

class CodeReviewerStateType extends AbstractEnumType
{
    public const OPEN     = 'open';
    public const REJECTED = 'rejected';
    public const ACCEPTED = 'accepted';

    public const    TYPE   = 'enum_code_reviewer_state_type';
    protected const VALUES = [self::OPEN, self::REJECTED, self::ACCEPTED];
}
