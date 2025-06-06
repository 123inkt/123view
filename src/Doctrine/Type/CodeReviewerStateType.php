<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class CodeReviewerStateType extends AbstractEnumType
{
    public const OPEN     = 'open';
    public const REJECTED = 'rejected';
    public const ACCEPTED = 'accepted';

    public const string  TYPE   = 'enum_code_reviewer_state_type';
    public const array   VALUES = [self::OPEN, self::REJECTED, self::ACCEPTED];
}
