<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Doctrine\Type;

class CodeReviewStateType extends AbstractEnumType
{
    public const OPEN     = 'open';
    public const CLOSED   = 'closed';

    public const    TYPE   = 'enum_code_review_state_type';
    protected const VALUES = [self::OPEN, self::CLOSED];
}
