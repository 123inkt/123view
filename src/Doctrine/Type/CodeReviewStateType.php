<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class CodeReviewStateType extends AbstractEnumType
{
    public const OPEN   = 'open';
    public const CLOSED = 'closed';

    public const string   TYPE   = 'enum_code_review_state_type';
    public const array    VALUES = [self::OPEN, self::CLOSED];
}
