<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class CodeReviewType extends AbstractEnumType
{
    public const COMMITS = 'commits';
    public const BRANCH  = 'branch';

    public const string TYPE   = 'enum_code_review_type';
    protected const array VALUES = [self::COMMITS, self::BRANCH];
}
