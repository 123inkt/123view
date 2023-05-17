<?php
declare(strict_types=1);

namespace DR\Review\Doctrine\Type;

class CodeReviewType extends AbstractEnumType
{
    public const COMMITS = 'commits';
    public const BRANCH  = 'branch';

    public const TYPE   = 'enum_code_review_type';
    public const VALUES = [self::COMMITS, self::BRANCH];
}
